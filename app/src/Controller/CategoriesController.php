<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use http\Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Categories;
use Symfony\Component\Validator\Validator\ValidatorInterface;
class CategoriesController extends AbstractController
{
    /**
     * @Route("/categories", methods={"GET","HEAD", "OPTIONS"})
     */
    public function getCategories(ManagerRegistry $doctrine): Response
    {
        $categories = $doctrine->getRepository(Categories::class)->findRootCategories();
        $response = new Response();
        $response->setContent(json_encode([
            'categories' => $this->prepareCategory($doctrine, $categories),
            'categoriesLinearStructure' => $doctrine->getRepository(Categories::class)->findAllArray()
        ]));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    /**
     * @Route("/categories", methods={"POST","HEAD", "OPTIONS"})
     */
    public function addCategory(ManagerRegistry $doctrine, Request $request, ValidatorInterface $validator): Response
    {
        $categoryName = $request->get('name');
        $categoryParent = $request->get('parent');
        $categoryParentEntity = null;
        if ($categoryParent) {
            $categoryParentEntity = $doctrine->getRepository(Categories::class)->findOneBy(['id' => $categoryParent]);
        }
        $category = new Categories();
        $category->setName($categoryName);
        $category->setParent($categoryParentEntity);

        $errors = $validator->validate($category);
        if (count($errors) > 0) {
            return new Response(
                json_encode(['status' => 'error']),
                Response::HTTP_OK,
                ['content-type' => 'application/json']);
        }

        $entityManager = $doctrine->getManager();
        $entityManager->persist($category);
        $entityManager->flush();

        $rootCategories = $doctrine->getRepository(Categories::class)->findRootCategories();
        $categoriesLinearStructure = $doctrine->getRepository(Categories::class)->findAllArray();

        return new Response(
            json_encode([
                'status' => 'ok',
                'categories' => $this->prepareCategory($doctrine, $rootCategories),
                'categoriesLinearStructure' => $categoriesLinearStructure]),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    private function prepareCategory($doctrine,  $categories): array
    {
        $arrCategory = [];
        foreach ($categories as $category) {
            $category['children'] = $this->getChildren($category, $doctrine);
            $arrCategory[] = $category;
        }
        return $arrCategory;
    }

    private function getChildren(&$category, $doctrine) {
        $children = $doctrine->getRepository(Categories::class)->findChildrensCategory($category['id']);

        if (count($children) === 0) return $children;

        foreach ($children as &$child) {
            $child['children'] = $this->getChildren($child, $doctrine);
        }

        return $children;

    }

    /**
     * @Route("/categories", methods={"DELETE","HEAD", "OPTIONS"})
     */
    public function deleteCategories(ManagerRegistry $doctrine, Request $request): Response
    {
        $id = $request->get('id');
        $entityManager = $doctrine->getManager();
        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            return new Response(
                json_encode(['status' => 'error']),
                Response::HTTP_OK,
                ['content-type' => 'application/json']);
        }

        $entityManager->remove($category);
        try {
            $entityManager->flush();
        } catch (Exception $e) {
            if (stristr($e->getMessage(), 'Cannot delete or update a parent row')) {
                return new Response(
                    json_encode(['status' => 'error', 'errorMessage'=> 'Невозможно удалить категорию, которая содержит вложенные категории']),
                    Response::HTTP_OK,
                    ['content-type' => 'application/json']);
            }
        }


        $rootCategories = $doctrine->getRepository(Categories::class)->findRootCategories();
        $categoriesLinearStructure = $doctrine->getRepository(Categories::class)->findAllArray();
        return new Response(
            json_encode([
                'status' => 'ok',
                'categories' => $this->prepareCategory($doctrine, $rootCategories),
                'categoriesLinearStructure' => $categoriesLinearStructure]),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );

    }

    /**
     * @Route("/categories", methods={"PUT","HEAD", "OPTIONS"})
     */
    public function updateCategories(ManagerRegistry $doctrine, Request $request): Response
    {
        $id = $request->get('id');
        $name = $request->get('name');
        $entityManager = $doctrine->getManager();
        $category = $entityManager->getRepository(Categories::class)->find($id);

        if (!$category) {
            return new Response(
                json_encode(['status' => 'error']),
                Response::HTTP_OK,
                ['content-type' => 'application/json']);
        }

        $category->setName($name);
        $entityManager->flush();

        return new Response(
            json_encode([
                'status' => 'ok',
                'categories' => $this->prepareCategory($doctrine, $doctrine->getRepository(Categories::class)->findRootCategories()),
                'categoriesLinearStructure' => $doctrine->getRepository(Categories::class)->findAllArray()
            ],
            ),
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );

    }
}
