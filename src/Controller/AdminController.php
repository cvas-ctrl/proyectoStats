<?php

namespace App\Controller;

use App\Entity\Elemento;
use App\Entity\Categoria;
use App\Entity\Valoracion;
use App\Repository\ElementoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_site')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig');
    }

    #[Route('/admin/load', name: 'data_load_api')]
    public function data_load(HttpClientInterface $httpClient, EntityManagerInterface $entityManager): Response
    {
        $endpoints = [
            'Personajes' => 'https://rickandmortyapi.com/api/character',
            'Localizaciones' => 'https://rickandmortyapi.com/api/location',
            'Episodios' => 'https://rickandmortyapi.com/api/episode'
        ];

        foreach ($endpoints as $nombreCat => $url) {
            $categoria = $entityManager->getRepository(Categoria::class)->findOneBy(['nombre' => $nombreCat]);
            if (!$categoria) {
                $categoria = new Categoria();
                $categoria->setNombre($nombreCat);
                $entityManager->persist($categoria);
            }

            $response = $httpClient->request('GET', $url, ['verify_peer' => false]);
            $data = $response->toArray();

            foreach ($data['results'] as $item) {
                $elemento = $entityManager->getRepository(Elemento::class)->findOneBy([
                    'idApi' => $item['id'],
                    'categoria' => $categoria
                ]);

                if (!$elemento) {
                    $elemento = new Elemento();
                    $elemento->setIdApi($item['id']);
                    $elemento->setCategoria($categoria);
                    $elemento->setPuntuacionPromedio(0);
                    $elemento->setTotalValoraciones(0);
                    $entityManager->persist($elemento);
                }

                $elemento->setNombre($item['name']);

                if (isset($item['image'])) {
                    $imageUrl = $item['image'];
                } else {
                    $imageUrl = match ($nombreCat) {
                        'Localizaciones' => 'https://images5.alphacoders.com/633/thumb-1920-633256.jpg',
                        'Episodios'      => 'https://images5.alphacoders.com/133/thumb-1920-1335149.jpg'
                    };
                }
                $elemento->setImagenUrl($imageUrl);

                $extra = [];
                if ($nombreCat === 'Personajes') {
                    $extra = ['status' => $item['status'], 'species' => $item['species']];
                } elseif ($nombreCat === 'Localizaciones') {
                    $extra = ['type' => $item['type'], 'dimension' => $item['dimension']];
                } else {
                    $extra = ['air_date' => $item['air_date'], 'episode' => $item['episode']];
                }
                $elemento->setDatosExtra($extra);
            }
        }

        $entityManager->flush();
        $this->addFlash('success', 'Sincronización completada con imágenes de respaldo');

        return $this->redirectToRoute('admin_site');
    }

    #[Route('/admin/gestion', name: 'admin_gestion')]
    public function gestion(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->request->has('new_categoria')) {
            $categoria = new Categoria();
            $categoria->setNombre($request->request->get('nombre'));
            $categoria->setImagenUrl($request->request->get('imagen_url'));

            $entityManager->persist($categoria);
            $entityManager->flush();
            $this->addFlash('success', 'Categoría creada');
        }

        if ($request->request->has('new_elemento')) {
            $elemento = new Elemento();
            $elemento->setNombre($request->request->get('nombre'));
            $elemento->setImagenUrl($request->request->get('imagen_url'));

            $cat = $entityManager->getRepository(Categoria::class)->find($request->request->get('categoria_id'));
            $elemento->setCategoria($cat);

            $elemento->setPuntuacionPromedio(0);
            $elemento->setTotalValoraciones(0);

            $entityManager->persist($elemento);
            $entityManager->flush();
            $this->addFlash('success', 'Elemento añadido');
        }

        $categorias = $entityManager->getRepository(Categoria::class)->findAll();

        return $this->render('admin/gestion.html.twig', [
            'categorias' => $categorias
        ]);
    }

    #[Route('/admin/categoria/borrar/{id}', name: 'admin_cat_borrar')]
    public function borrarCat(Categoria $cat, EntityManagerInterface $em): Response {
        $em->remove($cat);
        $em->flush();

        $this->addFlash('success', 'Categoría eliminada.');
        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/admin/elemento/borrar/{id}', name: 'admin_el_borrar')]
    public function borrarEl(Elemento $el, EntityManagerInterface $em): Response {
        $em->remove($el);
        $em->flush();

        $this->addFlash('success', 'Elemento eliminado');
        return $this->redirectToRoute('admin_gestion');
    }

    #[Route('/admin/elemento/editar/{id}', name: 'admin_el_editar')]
    public function editarElemento(Elemento $elemento, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $elemento->setNombre($request->request->get('nombre'));
            $elemento->setImagenUrl($request->request->get('imagen_url'));

            $cat = $em->getRepository(Categoria::class)->find($request->request->get('categoria_id'));
            $elemento->setCategoria($cat);

            $extraData = $request->request->all('extra');
            $elemento->setDatosExtra($extraData);

            $em->flush();

            $this->addFlash('success', 'Expediente actualizado');
            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/editar_elemento.html.twig', [
            'elemento' => $elemento,
            'categorias' => $em->getRepository(Categoria::class)->findAll()
        ]);
    }

    #[Route('/admin/categoria/editar/{id}', name: 'admin_cat_editar')]
    public function editarCategoria(Categoria $categoria, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $categoria->setNombre($request->request->get('nombre'));
            $categoria->setImagenUrl($request->request->get('imagen_url'));

            $em->flush();

            $this->addFlash('success', 'Categoría');
            return $this->redirectToRoute('admin_gestion');
        }

        return $this->render('admin/editar_categoria.html.twig', [
            'categoria' => $categoria
        ]);
    }

    #[Route('/admin/stats', name: 'admin_stats')]
    public function stats(EntityManagerInterface $em): Response
    {
        $topElementos = $em->createQuery('
        SELECT e.nombre, e.imagenUrl, e.puntuacionPromedio as promedio,
               e.totalValoraciones as totalVotos, c.nombre as catNombre
        FROM App\Entity\Elemento e
        JOIN e.categoria c
        ORDER BY e.puntuacionPromedio DESC
    ')
            ->setMaxResults(10)
            ->getResult();

        $statsCategorias = $em->createQuery('
        SELECT c.nombre as nombre,
               AVG(v.puntuacion) as promedio,
               COUNT(v.id) as totalVotos
        FROM App\Entity\Valoracion v
        JOIN v.elemento e
        JOIN e.categoria c
        GROUP BY c.id
    ')->getResult();

        $totalGeneralVotos = $em->getRepository(\App\Entity\Valoracion::class)->count([]);

        return $this->render('admin/stats.html.twig', [
            'topElementos' => $topElementos,
            'statsCategorias' => $statsCategorias,
            'totalGeneralVotos' => $totalGeneralVotos
        ]);
    }

    #[Route('/admin/usuarios', name: 'admin_usuarios')]
    public function usuarios(EntityManagerInterface $em): Response
    {
        $usuarios = $em->getRepository(\App\Entity\User::class)->findAll();

        return $this->render('admin/usuarios.html.twig', [
            'usuarios' => $usuarios
        ]);
    }

    #[Route('/admin/usuarios/rol/{id}', name: 'admin_user_rol')]
    public function cambiarRol(\App\Entity\User $user, EntityManagerInterface $em): Response
    {
        $roles = $user->getRoles();

        if (in_array('ROLE_ADMIN', $roles)) {
            $user->setRoles(['ROLE_USER']);
            $this->addFlash('success', 'Usuario degradado');
        } else {
            $user->setRoles(['ROLE_ADMIN']);
            $this->addFlash('success', 'Nuevo Administrador');
        }

        $em->flush();
        return $this->redirectToRoute('admin_usuarios');
    }

    #[Route('/admin/usuarios/borrar/{id}', name: 'admin_user_borrar')]
    public function borrarUsuario(\App\Entity\User $user, EntityManagerInterface $em): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'No puedes eliminarte');
            return $this->redirectToRoute('admin_usuarios');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Usuario eliminado.');
        return $this->redirectToRoute('admin_usuarios');
    }

    #[Route('/admin/comentarios', name: 'admin_comentarios')]
    public function listarComentarios(EntityManagerInterface $em): Response
    {
        $query = $em->createQuery(
            'SELECT v FROM App\Entity\Valoracion v
         WHERE v.comentario IS NOT NULL
         AND v.comentario != \'\'
         ORDER BY v.fechaCreacion DESC'
        );
        $comentarios = $query->getResult();

        return $this->render('admin/comentarios.html.twig', [
            'comentarios' => $comentarios
        ]);
    }

    #[Route('/admin/comentario/borrar/{id}', name: 'admin_comentario_borrar')]
    public function borrarComentario(\App\Entity\Valoracion $valoracion, EntityManagerInterface $em): Response
    {
        $em->remove($valoracion);
        $em->flush();

        $this->addFlash('success', 'Reseña eliminada');
        return $this->redirectToRoute('admin_comentarios');
    }

    #[Route('/admin/usuarios/detalle/{id}', name: 'admin_user_detalle')]
    public function detalleUsuario(\App\Entity\User $user, EntityManagerInterface $em): Response
    {
        return $this->render('admin/usuario.html.twig', [
            'user' => $user
        ]);
    }
}
