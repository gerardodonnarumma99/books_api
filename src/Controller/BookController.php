<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\BookEntityRepository;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Serializer;

use Symfony\Component\Validator\Validator\ValidatorInterface;


class BookController extends AbstractController
{
    /**
     * @param
     * @return Response
     *
     * @Route("/api/v1/books", methods={"post"})
     *
     * @SWG\Post(
     *      description="Crea un libro",
     *       @SWG\Parameter(
     *          name="body",
     *          description="Dati di un libro",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref=@Model(type=Book::class))
     *     )
     *     ),
     * @SWG\Response(
     *          response=204,
     *          description="Libro creato",
     *     ),
     * @SWG\Response(
     *          response=400,
     *          description="Errore nella creazione del libro",
     *     )
     * )
     * @SWG\Tag(name="Books")
     *
     * */
    public function addBook(Request $request,ValidatorInterface $validator)
    {
	try{
	   $bookRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);

        $book=new Book();
        $book->setTitle($bookRequest['title']);
        $book->setPrice($bookRequest['price']);

	    //Controllo validazioni
	   $errors = $validator->validate($book);
    	   if (count($errors) > 0)
        	return new Response(null,400);
 
	   $entityManager = $this->getDoctrine()->getManager();
	   $entityManager->persist($book);
	   $entityManager->flush();
       $jsonContent=$this->get('serializer')->serialize($book,'json');

	   return new Response(null,200);
	}
	catch(\JsonException $exception) {
	   return new Response($exception->getMessage(),400);
	}
    }

    /**
     * @param $id
     * @return JsonResponse
     *
     * @Route("/api/v1/books/{id}", methods={"get"})
     *
     * @SWG\Get(
     *      description="Recupera un libro avendo un id",
     *      @SWG\Parameter(
     *          name="id",
     *          description="id del libro",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=200,
     *          description="Il libro trovato",
     *          @SWG\Schema(ref=@Model(type=Book::class))
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Libro non trovato",
     *     )
     * )
     * @SWG\Tag(name="Books")
     *
     * */
    public function getOneBook($id)
    {
	$repository=$this->getDoctrine()->getRepository(Book::class);
	$book=$repository->find($id);
	
	if($book==null)
	 return new Response(null,400);

	$jsonContent=$this->get('serializer')->serialize($book,'json');
        
	return new Response($jsonContent,200);
    }

    /**
     * @param
     * @return JsonResponse
     *
     * @Route("/api/v1/books", methods={"get"})
     *
     * @SWG\Get(
     *      description="Recupera tutti i libri",
     *      @SWG\Response(
     *          response=200,
     *          description="La lista di tutti i libri",
     *          @SWG\Items(@Model(type=Book::class))
     *     ),
     *      @SWG\Response(
     *          response=204,
     *          description="Lista dei libri vuota",
     *     )
     * )
     * @SWG\Tag(name="Books")
     *
     * */
    public function getListBook()
    {
        $repository=$this->getDoctrine()->getRepository(Book::class);

	    $books=$repository->findAll();

        if($books==null)
            return new JsonResponse([],204);
        
	    return new Response($this->get('serializer')->serialize($books,'json'),200);
    }

    /**
     * @param $id
     * @return Response
     *
     * @Route("/api/v1/books/{id}", methods={"delete"})
     *
     * @SWG\Delete(
     *      description="Elimina un libro avendo un id",
     *      @SWG\Parameter(
     *          name="id",
     *          description="id del libro",
     *          in="path",
     *          type="integer",
     *          required=true,
     *      ),
     *      @SWG\Response(
     *          response=204,
     *          description="Libro eliminato"
     *     ),
     *      @SWG\Response(
     *          response=400,
     *          description="Libro non trovato",
     *     )
     * )
     * @SWG\Tag(name="Books")
     *
     * */
    public function deleteBook($id)
    {
	$entityManager = $this->getDoctrine()->getManager();
	$book=$entityManager->getRepository(Book::class)->find($id);

	if($book==null)
	 return new Response(null,400);

	$entityManager->remove($book);
	$entityManager->flush();
        
	return new Response(null,204);
    }

    /**
     * @param $id
     * @return Response
     *
     * @Route("/api/v1/books", methods={"put"})
     *
     * @SWG\Put(
     *      description="Modifica un libro",
     *       @SWG\Parameter(
     *          name="body",
     *          description="Dati di un libro",
     *          in="body",
     *          required=true,
     *          @SWG\Schema(ref=@Model(type=Book::class))
     *     )
     *     ),
     * @SWG\Response(
     *          response=204,
     *          description="Libro modificato",
     *     ),
     * @SWG\Response(
     *          response=400,
     *          description="Libro non trovato",
     *     )
     * )
     * @SWG\Tag(name="Books")
     *
     * */
    public function modifyBook($id,Request $request,ValidatorInterface $validator)
    {
	    try{
	        $bookRequest=json_decode($request->getContent(),1,512,JSON_THROW_ON_ERROR);
	        $entityManager=$this->getDoctrine()->getManager();
	        $book=$entityManager->getRepository(Book::class)->find($id);
	        if($book==null)
	            return new Response(null,400);


            $book->setTitle($bookRequest['title']);
            $book->setPrice($bookRequest['price']);

	        //Controllo validazioni
	        $errors = $validator->validate($book);
    	    if (count($errors) > 0)
        	    return new Response(null,400);

	        $entityManager->persist($book);
	        $entityManager->flush();

	        return new Response(null,204);
	    }
	    catch(\JsonException $exception) {
	        return new Response($exception->getMessage(),400);
        }
    }
}
