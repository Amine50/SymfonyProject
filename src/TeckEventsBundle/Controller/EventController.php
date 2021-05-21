<?php

namespace TeckEventsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use TeckEventsBundle\Entity\Address;
use TeckEventsBundle\Entity\Categorie;
use TeckEventsBundle\Entity\Event;
use TeckEventsBundle\Entity\Notification;
use TeckEventsBundle\Entity\Participation;

class EventController extends Controller
{
    public function newEventAction(Request $request)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        if ($user == null || empty($user) || $user == "anon.") {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $id_user = $user->getId();
            $currentUser = $this->getDoctrine()->getRepository(User::class)->find($id_user);
                 $event = new Event();
               if ($request->getMethod() == "POST") {
                   $dateEvent= new \DateTime($request->get('form_date'));
                    if($dateEvent< new \DateTime()){
                        $this->get('session')->getFlashBag()->add('notice', 'Choose a date after the current date !');
                        $this->redirectToRoute('new_event');
                    }
                    else{
                        $filephoto = $request->files->get('form_photo');
                        $fileNamephoto = $this->generateUniqueFileName() . '.' . $filephoto->guessExtension();
                        $filephoto->move($this->getParameter('brochures_directory'), $fileNamephoto);
                        $event->setPhotoEvent($fileNamephoto);
                        $event->setAdminEvent($currentUser);
                        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                            $event->setStatusEvent("Approved");
                        }
                        else{
                            $event->setStatusEvent("Waiting");
                        }
                        $event->setDateEvent($dateEvent);
                        $event->setDateCreationEvent(new \DateTime());
                        $event->setNameEvent($request->get('form_name'));
                        $localisation = $this->getDoctrine()->getRepository(Address::class)->findOneBy(array('addressExact' => $request->get('form_address'), 'cityAddress' => $request->get('form_city')));
                        if ($localisation==null||empty($localisation)){
                            $localisation = new Address();
                            $localisation->setAddressExact($request->get('form_address'));
                            $localisation->setCityAddress($request->get('form_city'));
                            $localisation->setDescriptionAddress($request->get('form_address'));
                            $em1 = $this->getDoctrine()->getManager();
                            $em1->persist($localisation);
                            $em1->flush();
                        }
                        $event->setAddressEvent($this->getDoctrine()->getRepository(Address::class)->find($localisation->getId()));


                        $cat = $this->getDoctrine()->getRepository(Categorie::class)->find($request->get('form_categorie'));
                        $event->setCategorieEvent($cat);
                        $event->setDescriptionEvent($request->get('form_description'));

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($event);
                        $em->flush();

                        $notification = new Notification();
                        $notification
                            ->setTitle('Event Added')
                            ->setDescription($event->getAdminEvent()->getFirstName().' added a new event named : '.$event->getNameEvent())
                            ->setParameters(array('id' => $event->getId()))
                        ;
                        foreach ($this->getDoctrine()->getRepository(User::class)->findAll() as $u){
                            if(in_array('ROLE_ADMIN', $u->getRoles())){
                                $notification->setIdUser($u);
                            }
                        }
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($notification);
                        $em->flush();
                        $this->get('session')->getFlashBag()->add('notice1','Your Event '.$event->getNameEvent().' has been successfully added !');
                        return $this->redirectToRoute('events_by_user');
                    }
                }
        }

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            return $this->render('@TeckEvents/Events/Admin/NewEvent.html.twig', array('categories' => $categories));
        }
        else {
            return $this->render( '@TeckEvents/Events/Client/NewEvent.html.twig', array('categories' => $categories));
        }

    }
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }

    function searchAddressAction(Request $request){
        $plein_text = $request->get('plein_text');
        $city = $request->get('city');

        $repository = $this->getDoctrine()->getRepository(Address::class);
        $results=$repository->createQueryBuilder('a')
            ->where("a.addressExact LIKE '%$plein_text%'")
            ->andwhere("a.cityAddress = '$city'")
            ->getQuery()->getArrayResult();
        return new JsonResponse($results);
    }

    function newCategorieAction(Request $request){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user == null || empty($user) || $user == "anon.") {
            return $this->redirectToRoute('fos_user_security_login');
        }
        else{
            if ($request->getMethod() == "POST") {
                $catexiste = $this->getDoctrine()->getRepository(Categorie::class)->findOneBy(array('nameCategorie' => $request->get('form_name')));
                if ($catexiste = null || empty($catexiste)){
                    $cat = new Categorie();
                    $cat->setNameCategorie($request->get('form_name'));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($cat);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('notice', 'Your Categorie '.$cat->getNameCategorie().' successfully added');

                    return $this->redirectToRoute('new_categorie');
                }else{
                    $this->get('session')->getFlashBag()->add('notice1', 'Your Categorie '.$request->get('form_name').' already exist !');

                    return $this->redirectToRoute('new_categorie');
                }

            }
            $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();

            return $this->render('@TeckEvents/Events/Admin/NewCategorie.html.twig',array('categories'=>$categories));
        }
    }


    public function getEventByUserAction(Request $request){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        if ($user == null || empty($user) || $user == "anon.") {
            return $this->redirectToRoute('fos_user_security_login');
        }

        $id_user = $user->getId();
        $currentUser = $this->getDoctrine()->getRepository(User::class)->find($id_user);
        if ($request->getMethod() == "POST") {
            $dateRequest = $request->get('search_date');
            $nameRequest = $request->get('search_name');
            $locationRequest = $request->get('search_location');
         if($dateRequest =='' && $nameRequest =='' && $locationRequest ==''){
             return $this->redirectToRoute('events_by_user');
         }else{
             if($dateRequest == null || $dateRequest== ''){
                 $listevnents = $this->getDoctrine()->getRepository(Event::class)
                     ->createQueryBuilder('e')
                     ->join('e.addressEvent', 'a')
                     ->join('e.adminEvent', 'u')
                     ->where("e.nameEvent LIKE '%$nameRequest%' AND u.id =".$currentUser->getId()."  AND a.addressExact LIKE '%$locationRequest%'")
                     ->getQuery()
                     ->getResult();
             }else{
                 $listevnents = $this->getDoctrine()->getRepository(Event::class)
                     ->createQueryBuilder('e')
                     ->join('e.addressEvent', 'a')
                     ->join('e.adminEvent', 'u')
                     ->where("e.nameEvent LIKE '%$nameRequest%' AND u.id =".$currentUser->getId()." AND e.dateEvent='".$dateRequest."' AND a.addressExact LIKE '%$locationRequest%'")
                     ->getQuery()
                     ->getResult();
             }
         }
        }else{
            $listevnents =$this->getDoctrine()->getRepository(Event::class)->findBy(array('adminEvent' => $currentUser));
        }
        if (empty($listevnents)) {
            $this->get('session')->getFlashBag()->add('notice', 'no event found !');
        }

        return $this->render('@TeckEvents/Events/Client/AllEventByUser.html.twig', array('events'=>$listevnents) );
    }
    public function getAllEventAction(Request $request){
        $listevnents =$this->getDoctrine()->getRepository(Event::class)->findBy(array('statusEvent'=>"Approved"));
        if ($request->getMethod() == "POST") {
            $dateRequest = $request->get('search_date');
            $nameRequest = $request->get('search_name');
            $locationRequest = $request->get('search_location');
            if($dateRequest =='' && $nameRequest =='' && $locationRequest ==''){
                return $this->redirectToRoute('all_events');
            }else{
                if($dateRequest == null || $dateRequest== ''){
                    $listevnents = $this->getDoctrine()->getRepository(Event::class)
                        ->createQueryBuilder('e')
                        ->join('e.addressEvent', 'a')
                        ->where("e.nameEvent LIKE '%$nameRequest%' AND e.statusEvent ='Approved'  AND a.cityAddress LIKE '%$locationRequest%'")
                        ->getQuery()
                        ->getResult();

                }else{
                    $listevnents = $this->getDoctrine()->getRepository(Event::class)
                        ->createQueryBuilder('e')
                        ->join('e.addressEvent', 'a')
                        ->where("e.nameEvent LIKE '%$nameRequest%' AND e.statusEvent ='Approved' AND e.dateEvent='".$dateRequest."' AND a.cityAddress LIKE '%$locationRequest%'")
                        ->getQuery()
                        ->getResult();
                }
            }
        }

        if (empty($listevnents)) {
            $this->get('session')->getFlashBag()->add('notice1', 'No event found !');
        }

        return $this->render('@TeckEvents/Events/Client/AllEvent.html.twig', array('events'=>$listevnents) );
    }

    public function getAllEventAdminAction(Request $request){
        $listevnents =$this->getDoctrine()->getRepository(Event::class)->findAll();
        if (empty($listevnents)) {
            $this->get('session')->getFlashBag()->add('notice', 'no event found !');
        }

        $notifs =$this->getDoctrine()->getRepository(Event::class)->findAll();

        return $this->render('@TeckEvents/Events/Admin/AllEvent.html.twig', array('events'=>$listevnents,'notifs'=>$notifs) );
    }


    public function approveEventAction($id){
       $eventInfo = $this->getDoctrine()->getRepository(Event::class)->find($id);
         $eventInfo->setStatusEvent('Approved');
        $em = $this->getDoctrine()->getManager();
         $em->persist($eventInfo);
         $em->flush();
         $this->get('session')->getFlashBag()->add('notice1', $eventInfo->getNameEvent().' event has been successfully approved !');
        $notification = new Notification();
        $notification
            ->setTitle('Event Accepted')
            ->setDescription('"'.$eventInfo->getNameEvent().'" has been accepted by admin')
            ->setParameters(array('id' => $eventInfo->getId()))
        ;

        $notification->setIdUser($eventInfo->getAdminEvent());

        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
         return $this->redirectToRoute('all_events_Admin');
    }

    public function refuseEventAction($id){
        $eventInfo = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $eventInfo->setStatusEvent('Refused');
        $em = $this->getDoctrine()->getManager();
        $em->persist($eventInfo);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', $eventInfo->getNameEvent().' event has been successfully refused !');

        $notification = new Notification();
        $notification
            ->setTitle('Event Refused')
            ->setDescription('"'.$eventInfo->getNameEvent().'" has been refused by admin')
            ->setParameters(array('id' => $eventInfo->getId()))
        ;

        $notification->setIdUser($eventInfo->getAdminEvent());
        $em = $this->getDoctrine()->getManager();
        $em->persist($notification);
        $em->flush();
        return $this->redirectToRoute('all_events_Admin');

    }

    public function deleteEventAction($id){

        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $eventInfo=$event;
        if($event==null) {
            return new Response("Event non trouvé");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
        $this->get('session')->getFlashBag()->add('notice', $eventInfo->getNameEvent().' event has been successfully removed !');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $not = new Notification();
            $not
                ->setTitle('Event Deleted')
                ->setDescription('"'.$eventInfo->getNameEvent().'" has been deleted by admin')
                ->setParameters(array('id' => $eventInfo->getId()))
            ;

            $not->setIdUser($eventInfo->getAdminEvent());
            $em = $this->getDoctrine()->getManager();
            $em->persist($not);
            $em->flush();
            return $this->redirectToRoute('all_events_Admin');
        }
        else {
            $n = new Notification();
            $n
                ->setTitle('Event Deleted')
                ->setDescription('"'.$eventInfo->getNameEvent().'" has been deleted by admin Event')
                ->setParameters(array('id' => $eventInfo->getId()))
            ;

            $n->setIdUser($eventInfo->getAdminEvent());
            foreach ($this->getDoctrine()->getRepository(User::class)->findAll() as $user){
                if(in_array('ROLE_ADMIN', $user->getRoles())){
                    $n->setIdUser($user);
                }
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($n);
            $em->flush();

            return $this->redirectToRoute('events_by_user');
        }
    }

    public function getEventByIdAction($id)
    {
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $type='';
        $statusP='';
        if ($user != "anon.") {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $id_user = $user->getId();
            $currentUser = $this->getDoctrine()->getRepository(User::class)->find($id_user);
            if($event->getAdminEvent() == $currentUser){
                $type="Organizer";
            }
            $partcipation = $this->getDoctrine()->getRepository(Participation::class)->findOneBy(array('idEvent'=>$event,'idUser'=>$currentUser));
            if($partcipation!=null){
                $statusP=$partcipation->getStatus();
            }
        }
        $listLastevents = $this->getDoctrine()->getRepository(Event::class)->findBy(array(),array('dateEvent' => 'DESC'),4);


        $partN = ['yes' => $this->countParticipation($event->getId(),'Interested'),
            'no' => $this->countParticipation($event->getId(),'Not Interested'),
            'maybe' => $this->countParticipation($event->getId(),'May Be')];

            
        return $this->render('@TeckEvents/Events/Client/EventById.html.twig', array('event'=>$event,'type'=>$type,'lastEvents'=>$listLastevents,'stausPart'=>$statusP,'partN'=>$partN) );

    }

    public function updateEventAction(Request $request,$id){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        if ($user == null || empty($user) || $user == "anon.") {
            return $this->redirectToRoute('fos_user_security_login');
        } else {
            $id_user = $user->getId();
            $currentUser = $this->getDoctrine()->getRepository(User::class)->find($id_user);
            $event = $event;
            if($event==null) {
                return new Response("Event non trouvé");
            }

            if ($request->getMethod() == "POST") {
                $dateEvent= new \DateTime($request->get('form_date'));
                if($dateEvent< new \DateTime()){

                    $this->get('session')->getFlashBag()->add('notice', 'Choose a date after the current date !');
                    return $this->redirectToRoute('update_event',array('id'=>$id));
                }else{
                    $filephoto = $request->files->get('form_photo');
                    $fileNamephoto = $this->generateUniqueFileName() . '.' . $filephoto->guessExtension();
                    $filephoto->move($this->getParameter('brochures_directory'), $fileNamephoto);
                    $event->setPhotoEvent($fileNamephoto);
                    $event->setAdminEvent($currentUser);
                    if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
                        $event->setStatusEvent("Approved");
                    }
                    else{
                        $event->setStatusEvent("Waiting");
                    }
                    $event->setDateEvent($dateEvent);
                    $event->setNameEvent($request->get('form_name'));
                    $localisation = $this->getDoctrine()->getRepository(Address::class)->findOneBy(array('addressExact' => $request->get('form_address'), 'cityAddress' => $request->get('form_city')));
                    if ($localisation==null||empty($localisation)){
                        $localisation = new Address();
                        $localisation->setAddressExact($request->get('form_address'));
                        $localisation->setCityAddress($request->get('form_city'));
                        $localisation->setDescriptionAddress($request->get('form_address'));
                        $em1 = $this->getDoctrine()->getManager();
                        $em1->persist($localisation);
                        $em1->flush();
                    }
                    $event->setAddressEvent($this->getDoctrine()->getRepository(Address::class)->find($localisation->getId()));


                    $cat = $this->getDoctrine()->getRepository(Categorie::class)->find($request->get('form_categorie'));
                    $event->setCategorieEvent($cat);
                    $event->setDescriptionEvent($request->get('form_description'));

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($event);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('notice1', ''.$event->getNameEvent().' has been succesfully updated !');
                    return $this->redirectToRoute('events_by_user');
                }


            }
        }
        return $this->render( '@TeckEvents/Events/Client/UpdateEvent.html.twig', array('categories' => $categories ,'event'=>$event));
    }

    public function participationEventAction($id,$status){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $id_user = $user->getId();
        $currentUser = $this->getDoctrine()->getRepository(User::class)->find($id_user);
        $event = $this->getDoctrine()->getRepository(Event::class)->find($id);
        $partcipation = $this->getDoctrine()->getRepository(Participation::class)->findOneBy(array('idEvent'=>$event,'idUser'=>$currentUser));
        if($partcipation == null){

            $partcipation = new Participation();
            $partcipation->setIdUser($currentUser);
            $partcipation->setIdEvent($event);
        }
        $partcipation->setStatus($status);
        $em = $this->getDoctrine()->getManager();
        $em->persist($partcipation);
        $em->flush();
        return $this->redirectToRoute('event_by_id',array('id'=>$id));

    }

    public function countParticipation($id_event,$status){
        $repository = $this->getDoctrine()->getRepository(Participation::class);
        $count = $repository->createQueryBuilder('p')
            ->where("p.status = '$status'")
            ->andWhere("p.idEvent ='$id_event'")
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $count;
    }

}