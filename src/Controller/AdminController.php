<?php

namespace App\Controller;

use App\Entity\Unit;
use App\Entity\Agent;
use App\Entity\Entity;
use App\Form\UnitType;
use App\Entity\Service;
use App\Form\AgentType;
use App\Form\EntityType;
use App\Entity\Direction;
use App\Form\ServiceType;
use App\Entity\Department;
use App\Form\DirectionType;
use App\Form\DepartmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * 
 * @Route("/presidence/admin/phone-book")
 */
class AdminController extends AbstractController
{

    private $manager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->manager = $entityManager;
   
    }

    /**@IsGranted("ROLE_ADMIN")
     * @Route("/", name="dashboard")
     */
    public function index(): Response
    {   
        return $this->render('/backend/dashboard.html.twig', [
            'agents' => $this->manager->getRepository(Agent::class)->findAll(),
            'departments' => $this->manager->getRepository(Department::class)->findAll(),
            'services' => $this->manager->getRepository(Service::class)->findAll(),
        ]);
    }

    /**@IsGranted("ROLE_ADMIN")
     * @Route("/agents", name="agents")
     */
    public function agents(Request $request): Response
    {  
        $agent = new Agent();
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && !$request->get('agent')){
           
            $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
            $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
            $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));
            $department = $this->manager->getRepository(Department::class)->find($request->get('department'));
            $fonction = $this->manager->getRepository(Service::class)->find($request->get('fonction'));
            
            $agent->setFirstName($request->get('firstName'));
            $agent->setLastName($request->get('lastName'));
            $agent->setEmail($request->get('email'));
            $agent->setPost($request->get('post'));
            $agent->setEntity($entity);
            $agent->setUnit($unit);
            $agent->setDirection($direction);
            $agent->setDepartment($department);
            $agent->setFonction($fonction);

           $this->manager->persist($agent);
           $this->manager->flush();

           $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
           $entities = $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']);
           $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
           $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
           $fonctions = $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC']);
           $agents = $this->manager->getRepository(Agent::class)->findBy([], ['id' => 'DESC']);

           $row = '';
           foreach($agents as $key => $agent){
            $option1 = "";
            $option2 = "";
            $option3 = "";
            $option4 = "";
            $option5 = "";

            foreach($entities as $entity){
                if($entity->getId() === $agent->getEntity()->getId()){
                    $option1 .= "<option value=".$entity->getId()." selected>".$entity->getName()."</option>";
                }else{
                    $option1 .= "<option value=".$entity->getId().">".$entity->getName()."</option>";
                }
            }

            foreach($units as $unit){
                if($unit->getId() === $agent->getUnit()->getId()){
                    $option2 .= "<option value=".$unit->getId()." selected>".$unit->getName()."</option>";
                }else{
                    $option2 .= "<option value=".$unit->getId().">".$unit->getName()."</option>";
                }
            }

            foreach($directions as $direction){
          
                if($direction->getId() === $agent->getDirection()->getId()){
                    $option3 .= "<option value=".$direction->getId()." selected>".$direction->getName()."</option>";
                }else{
                    $option3 .= "<option value=".$direction->getId().">".$direction->getName()."</option>";
                }
            }

            foreach($departments as $department){
                if($department->getId() === $agent->getDepartment()->getId()){
                    $option4 .= "<option value=".$department->getId()." selected>".$department->getName()."</option>";
                }else{
                    $option4 .= "<option value=".$department->getId().">".$department->getName()."</option>";
                }
            }

            foreach($fonctions as $fonction){
                if($fonction->getId() === $agent->getFonction()->getId()){
                    $option5 .= "<option value=".$fonction->getId()." selected>".$fonction->getName()."</option>";
                }else{
                    $option5 .= "<option value=".$fonction->getId().">".$fonction->getName()."</option>";
                }
            }

           $row .= '
           <tr id="tr-'.$unit->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$agent->getFirstName().'</td>
           <td>'.$agent->getLastName().'</td>
           <td>'.$agent->getEmail().'</td>
           <td>'.$agent->getPost().'</td>
           <td>'.$agent->getFonction().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$unit->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$unit->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$unit->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$unit->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$unit->getName().'? Toutes les données liées à cette entité seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$unit->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$unit->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une entité</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$agent->getId().'">
                               <div class="row">
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="firstname">Nom</label>
                                           <input type="text" name="firstName" class="form-control" value="'.$agent->getFirstName().'">
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="lastname">Prénom</label>
                                           <input type="text" name="lastName" class="form-control" value="'.$agent->getLastName().'">
                                       </div>
                                   </div>
                               </div>
                               <div class="row">
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="email">Email</label>
                                        <input type="text" name="email" class="form-control" value="'.$agent->getEmail().'">
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="post">N° poste</label>
                                        <input type="text" name="post" class="form-control" value="'.$agent->getPost().'">
                                       </div>
                                   </div>
                               </div>
                               <div class="row">
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="entity">Entité</label>
                                            <select class="form-control" id="entity" name="entity">
                                                '.$option1.'
                                            </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="unit">Unité</label>
                                             <select class="form-control" id="unit" name="unit">
                                            '.$option2.'
                                            </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="row">
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="direction">Direction</label>
                                           <select class="form-control" id="direction" name="direction">
                                                '.$option3.'
                                               </select>
                                           </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="department">Département</label>
                                                   <select class="form-control" id="department" name="department">
                                                   '.$option4.'
                                               </select>
                                       </div>
                                   </div>
                               </div>
                               <div class="row">
                                   <div class="col-md-12 col-sm-12 col-xs-12">
                                       <div class="form-group">
                                           <label for="fonction">Fonction</label>
                                             <select class="form-control" id="fonction" name="fonction">
                                                  '.$option5.'
                                               </select>
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="agent" value="'.$agent->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$agent->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['agent' => $agent->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('agent')){
  
            $agent = $this->manager->getRepository(Agent::class)->find($request->get('agent'));
            $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
            $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
            $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));
            $department = $this->manager->getRepository(Department::class)->find($request->get('department'));
            $fonction = $this->manager->getRepository(Service::class)->find($request->get('fonction'));
            
            $agent->setFirstName($request->get('firstName'));
            $agent->setLastName($request->get('lastName'));
            $agent->setEmail($request->get('email'));
            $agent->setPost($request->get('post'));
            $agent->setEntity($entity);
            $agent->setUnit($unit);
            $agent->setDirection($direction);
            $agent->setDepartment($department);
            $agent->setFonction($fonction);

            $this->manager->persist($agent);
            $this->manager->flush();


            $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
            $entities = $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']);
            $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
            $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
            $fonctions = $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC']);
            $agents = $this->manager->getRepository(Agent::class)->findBy([], ['id' => 'DESC']);
 
            $row = '';
            foreach($agents as $key => $agent){
             $option1 = "";
             $option2 = "";
             $option3 = "";
             $option4 = "";
             $option5 = "";
 
             foreach($entities as $entity){
                 if($entity->getId() === $agent->getEntity()->getId()){
                     $option1 .= "<option value=".$entity->getId()." selected>".$entity->getName()."</option>";
                 }else{
                     $option1 .= "<option value=".$entity->getId().">".$entity->getName()."</option>";
                 }
             }
 
             foreach($units as $unit){
                 if($unit->getId() === $agent->getUnit()->getId()){
                     $option2 .= "<option value=".$unit->getId()." selected>".$unit->getName()."</option>";
                 }else{
                     $option2 .= "<option value=".$unit->getId().">".$unit->getName()."</option>";
                 }
             }
 
             foreach($directions as $direction){
             
                 if($direction->getId() === $agent->getDirection()->getId()){
                     $option3 .= "<option value=".$direction->getId()." selected>".$direction->getName()."</option>";
                 }else{
                     $option3 .= "<option value=".$direction->getId().">".$direction->getName()."</option>";
                 }
             }
 
             foreach($departments as $department){
                 if($department->getId() === $agent->getDepartment()->getId()){
                     $option4 .= "<option value=".$department->getId()." selected>".$department->getName()."</option>";
                 }else{
                     $option4 .= "<option value=".$department->getId().">".$department->getName()."</option>";
                 }
             }
 
             foreach($fonctions as $fonction){
                 if($fonction->getId() === $agent->getFonction()->getId()){
                     $option5 .= "<option value=".$fonction->getId()." selected>".$fonction->getName()."</option>";
                 }else{
                     $option5 .= "<option value=".$fonction->getId().">".$fonction->getName()."</option>";
                 }
             }
 
            $row .= '
            <tr id="tr-'.$unit->getId().'">
            <td>'.($key+1).'</td>
            <td>'.$agent->getFirstName().'</td>
            <td>'.$agent->getLastName().'</td>
            <td>'.$agent->getEmail().'</td>
            <td>'.$agent->getPost().'</td>
            <td>'.$agent->getFonction().'</td>
            <td >
                <a type="submit" id="btn-modify-'.$unit->getId().'"
                    class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
                <a type="submit" id="delete-'.$unit->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                    data-target="#modal-danger">Supprimer 
                    <i class="fa fa-trash"></i></a>
                    <div class="modal fade" id="modal_delete_'.$unit->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$unit->getName().'</h5>
                                <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </a>
                            </div>
                            <div class="modal-body">
                                <p>Voulez-vous vraiment supprimer '.$unit->getName().'? Toutes les données liées à cette entité seront définitivement supprimées!</p>
                            </div>
                            <div class="modal-footer">
                                <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                                <a id="btn-delete-'.$unit->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="modal_edit_'.$unit->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header border-bottom-0">
                                <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une entité</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            
                            <div class="modal-body">
                            <form id="edit_form_'.$agent->getId().'">
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="firstname">Nom</label>
                                            <input type="text" name="firstName" class="form-control" value="'.$agent->getFirstName().'">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="lastname">Prénom</label>
                                            <input type="text" name="lastName" class="form-control" value="'.$agent->getLastName().'">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                         <input type="text" name="email" class="form-control" value="'.$agent->getEmail().'">
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="post">N° poste</label>
                                         <input type="text" name="post" class="form-control" value="'.$agent->getPost().'">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="entity">Entité</label>
                                             <select class="form-control" id="entity" name="entity">
                                                 '.$option1.'
                                             </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="unit">Unité</label>
                                              <select class="form-control" id="unit" name="unit">
                                             '.$option2.'
                                             </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="direction">Direction</label>
                                            <select class="form-control" id="direction" name="direction">
                                                 '.$option3.'
                                                </select>
                                            </div>
                                    </div>
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                        <div class="form-group">
                                            <label for="department">Département</label>
                                                    <select class="form-control" id="department" name="department">
                                                    '.$option4.'
                                                </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-xs-12">
                                        <div class="form-group">
                                            <label for="fonction">Fonction</label>
                                              <select class="form-control" id="fonction" name="fonction">
                                                   '.$option5.'
                                                </select>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="agent" value="'.$agent->getId().'">
                             </form>
                            </div>
                            <div class="modal-footer border-top-0 d-flex justify-content-center">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                                <button type="submit" id="edit-btn-'.$agent->getId().'" class="btn btn-warning">Modifier</button>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
             </tr>
            ';
            }
            return new JsonResponse(['agent' => $agent->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        $agent = new Agent();
        $form = $this->createForm(AgentType::class, $agent);
        $form->handleRequest($request);


        if($request->isXmlHttpRequest()){
          if($request->get('firstName')){
                
           $agent = new Agent();
   
           $agent->setFirstName($request->get('firstName'));
           $agent->setLastName($request->get('lastName'));
           $agent->setEmail($request->get('email'));
           $agent->setPost($request->get('post'));
        
           $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
           $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
           $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));
           $department = $this->manager->getRepository(Department::class)->find($request->get('department'));
           $fonction = $this->manager->getRepository(Service::class)->find($request->get('fonction'));

           $agent->setEntity($entity);
           $agent->setUnit($unit);
           $agent->setDirection($direction);
           $agent->setDepartment($department);
           $agent->setFonction($fonction);

           $this->manager->persist($agent);
           $this->manager->flush();

           return new JsonResponse(['agent' => $agent->getId()]);
          }
        }
        return $this->render('backend/agents/index.html.twig', [
            'agents' => $this->manager->getRepository(Agent::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'entities' => $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']),
            'units' => $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']),
            'directions' => $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']),
            'departments' => $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']),
            'fonctions' => $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC'])
        ]);
    }

        /**@IsGranted("ROLE_ADMIN")
     * @Route("/agents/delete", name="agent_remove")
     */
    public function agentRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('agent')){
            $agent = $this->manager->getRepository(Agent::class)->find($request->get('agent'));
         
            if($agent->getId()){

                $id = $agent->getId();

                $this->manager->remove($agent);
                $this->manager->flush();

              return new JsonResponse(['agent' => $id]);

            }
        }

        return null;
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/entities", name="entities")
     */
    public function entities(Request $request): Response
    {   
        $entity = new Entity();
        $form = $this->createForm(EntityType::class, $entity);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && $entity->getName()){

           $this->manager->persist($entity);
           $this->manager->flush();

           $entities = $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']);
            
           $row = '';
           foreach($entities as $key => $entity){
           $row .= '
           <tr id="tr-'.$entity->getId().'">
           <td>'.($key+1).'</td>
           <td id="td-'.$entity->getId().'">'.$entity->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$entity->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$entity->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$entity->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$entity->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$entity->getName().'? Toutes les données liées à cette entité seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$entity->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$entity->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une entité</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$entity->getId().'" action="">
                               <div class="row">
                                   <div class="col-md-12 col-sm-12 col-xs-12">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$entity->getId().'" class="form-control" value="'.$entity->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="entity" value="'.$entity->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$entity->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['entity' => $entity->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('entity')){
      
            $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
         
            $entity->setName($request->get('name'));
            
            $this->manager->persist($entity);
            $this->manager->flush();

            return new JsonResponse(['entity' => $entity->getId(), 'name' => $entity->getName()]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        
        return $this->render('backend/entities/index.html.twig', [
            'entities' => $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/entities/delete", name="entity_remove")
     */
    public function entityRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('entity')){
            $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
            
            if($entity->getId()){

                $id = $entity->getId();

                $this->manager->remove($entity);
                $this->manager->flush();

              return new JsonResponse(['entity' => $id]);

            }
        }

        return null;
    }

        /**
         * @IsGranted("ROLE_ADMIN")
     * @Route("/units", name="units")
     */
    public function units(Request $request): Response
    {   
        $unit = new Unit();
        $form = $this->createForm(UnitType::class, $unit);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && $unit->getName()){

           $this->manager->persist($unit);
           $this->manager->flush();

           $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
           $entities = $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']);
           $row = '';
           foreach($units as $key => $unit){
            $option = "";
            foreach($entities as $entity){
                if($entity->getId() === $unit->getEntity()->getId()){
                    $option .= "<option value=".$entity->getId()." selected>".$entity->getName()."</option>";
                }else{
                    $option .= "<option value=".$entity->getId().">".$entity->getName()."</option>";
                }
            }

           $row .= '
           <tr id="tr-'.$unit->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$unit->getEntity()->getName().'</td>
           <td id="td-'.$unit->getId().'">'.$unit->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$unit->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$unit->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$unit->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$unit->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$unit->getName().'? Toutes les données liées à cette entité seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$unit->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$unit->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une entité</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$unit->getId().'" action="">
                               <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <div class="form-group">
                                        <label for="entity">Entité</label>
                                        <select name="entity" class="form-control">
                                            '. $option .'
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-6 col-sm-16col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$unit->getId().'" class="form-control" value="'.$unit->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="unit" value="'.$unit->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$unit->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['unit' => $unit->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('unit')){
      
            $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
            $entity = $this->manager->getRepository(Entity::class)->find($request->get('entity'));
            
            $unit->setName($request->get('name'));
            $unit->setEntity($entity);
            
            
            $this->manager->persist($unit);
            $this->manager->flush();


            $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
            $entities = $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC']);
            
           $row = '';
           foreach($units as $key => $unit){

            $option = "";
            foreach($entities as $entity){
                if($entity->getId() === $unit->getEntity()->getId()){
                    $option .= "<option value=".$unit->getId()." selected>".$unit->getName()."</option>";
                }else{
                    $option .= "<option value=".$unit->getId().">".$unit->getName()."</option>";
                }
            }
           $row .= '
           
           <tr id="tr-'.$unit->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$unit->getEntity()->getName().'</td>
           <td id="td-'.$unit->getId().'">'.$unit->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$unit->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$unit->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$unit->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$unit->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$unit->getName().'? Toutes les données liées à cette entité seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$unit->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$unit->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une entité</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$unit->getId().'" action="">
                               <div class="row">
                                  <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="entity">Entité</label>
                                           <select name="entity" class="form-control">
                                            '. $option .'
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$unit->getId().'" class="form-control" value="'.$unit->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="unit" value="'.$unit->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$unit->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }

          
           return new JsonResponse(['unit' => $unit->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        
        return $this->render('backend/units/index.html.twig', [
            'units' => $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'entities' => $this->manager->getRepository(Entity::class)->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/units/delete", name="unit_remove")
     */
    public function unitRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('unit')){
            $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
            
            if($unit->getId()){

                $id = $unit->getId();

                $this->manager->remove($unit);
                $this->manager->flush();

              return new JsonResponse(['unit' => $id]);

            }

        }

        return null;
    }


            /**
             * @IsGranted("ROLE_ADMIN")
     * @Route("/directions", name="directions")
     */
    public function directions(Request $request): Response
    {   
        $direction = new Direction();
       
        $form = $this->createForm(DirectionType::class, $direction);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && $direction->getName()){

           $this->manager->persist($direction);
           $this->manager->flush();

           $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
           $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
           $row = '';

        foreach($directions as $key => $direction){
            $option = "";
            foreach($units as $unit){
                if($direction->getUnit()->getId() === $unit->getId()){
                    $option .= "<option value=".$unit->getId()." selected>".$unit->getName()."</option>";
                }else{
                    $option .= "<option value=".$unit->getId().">".$unit->getName()."</option>";
                }
            }

           $row .= '
           <tr id="tr-'.$unit->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$direction->getUnit()->getName().'</td>
           <td id="td-'.$direction->getId().'">'.$direction->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$direction->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$direction->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$direction->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$direction->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$direction->getName().'? Toutes les données liées à cette direction seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$direction->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$direction->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une direction</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$direction->getId().'" action="">
                               <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <div class="form-group">
                                        <label for="unit">Unité</label>
                                        <select name="unit" class="form-control">
                                            '. $option .'
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-6 col-sm-16col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$direction->getId().'" class="form-control" value="'.$direction->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="direction" value="'.$direction->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$direction->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['direction' => $unit->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('unit')){
      
            $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));
            $unit = $this->manager->getRepository(Unit::class)->find($request->get('unit'));
        
            $direction->setName($request->get('name'));
            $direction->setUnit($unit);
            
            
            $this->manager->persist($direction);
            $this->manager->flush();


            $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
            $units = $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC']);
            
           $row = '';
           foreach($directions as $key => $direction){
            $option = "";
            foreach($units as $unit){
                if($direction->getUnit()->getId() === $unit->getId()){
                    $option .= "<option value=".$unit->getId()." selected>".$unit->getName()."</option>";
                }else{
                    $option .= "<option value=".$unit->getId().">".$unit->getName()."</option>";
                }
            }
           $row .= '
           
           <tr id="tr-'.$direction->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$direction->getUnit()->getName().'</td>
           <td id="td-'.$direction->getId().'">'.$direction->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$direction->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$direction->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$direction->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$direction->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$direction->getName().'? Toutes les données liées à cette direction seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$direction->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$direction->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier une direction</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$direction->getId().'" action="">
                               <div class="row">
                                  <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="unit">Unité</label>
                                           <select name="unit" class="form-control">
                                            '. $option .'
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$direction->getId().'" class="form-control" value="'.$direction->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="direction" value="'.$direction->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$direction->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }

          
           return new JsonResponse(['direction' => $direction->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        
        return $this->render('backend/directions/index.html.twig', [
            'directions' => $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'units' => $this->manager->getRepository(Unit::class)->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/directions/delete", name="direction_remove")
     */
    public function DirectionRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('direction')){
            $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));
            
            if($direction->getId()){

                $id = $direction->getId();

                $this->manager->remove($direction);
                $this->manager->flush();

              return new JsonResponse(['direction' => $id]);

            }

        }

        return null;
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/departments", name="departments")
     */
    public function departments(Request $request): Response
    {   
        $department = new Department();
       
        $form = $this->createForm(DepartmentType::class, $department);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && $department->getName()){

           $this->manager->persist($department);
           $this->manager->flush();

           $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
           $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
           $row = '';

           foreach($departments as $key => $department){
            $option = "";
            foreach($directions as $direction){
                if($department->getId() === $department->getDirection()->getId()){
                    $option .= "<option value=".$direction->getId()." selected>".$direction->getName()."</option>";
                }else{
                    $option .= "<option value=".$direction->getId().">".$direction->getName()."</option>";
                }
            }
           $row .= '
           <tr id="tr-'.$department->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$department->getDirection()->getName().'</td>
           <td id="td-'.$department->getId().'">'.$department->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$department->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$department->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$department->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$department->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$department->getName().'? Toutes les données liées à ce département seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$department->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$department->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier un département</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$department->getId().'" action="">
                               <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <div class="form-group">
                                        <label for="direction">Direction</label>
                                        <select name="direction" class="form-control">
                                            '. $option .'
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-6 col-sm-16col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$department->getId().'" class="form-control" value="'.$department->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="department" value="'.$department->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$department->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['department' => $department->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('department')){

            $department = $this->manager->getRepository(Department::class)->find($request->get('department'));
            $direction = $this->manager->getRepository(Direction::class)->find($request->get('direction'));

            
            $department->setName($request->get('name'));
            $department->setDirection($direction);
            
            
            $this->manager->persist($department);
            $this->manager->flush();


            $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
            $directions = $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC']);
            
           $row = '';
           foreach($departments as $key => $department){
            $option = "";
            foreach($directions as $direction){
                if($department->getId() === $department->getDirection()->getId()){
                    $option .= "<option value=".$direction->getId()." selected>".$direction->getName()."</option>";
                }else{
                    $option .= "<option value=".$direction->getId().">".$direction->getName()."</option>";
                }
            }
           $row .= '
           
           <tr id="tr-'.$direction->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$department->getDirection()->getName().'</td>
           <td id="td-'.$department->getId().'">'.$department->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$department->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$department->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$department->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$department->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$department->getName().'? Toutes les données liées à ce département seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$department->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$department->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier un département</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$department->getId().'" action="">
                               <div class="row">
                                  <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="direction">Direction</label>
                                           <select name="direction" class="form-control">
                                            '. $option .'
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$department->getId().'" class="form-control" value="'.$department->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="department" value="'.$department->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$department->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }

          
           return new JsonResponse(['department' => $department->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        
        return $this->render('backend/departments/index.html.twig', [
            'departments' => $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'directions' => $this->manager->getRepository(Direction::class)->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/departments/delete", name="department_remove")
     */
    public function DepartmentRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('department')){
            $department = $this->manager->getRepository(Department::class)->find($request->get('department'));
            
            if($department->getId()){

                $id = $department->getId();

                $this->manager->remove($department);
                $this->manager->flush();

              return new JsonResponse(['department' => $id]);

            }

        }

        return null;
    }


    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/fonctions", name="fonctions")
     */
    public function fonctions(Request $request): Response
    {   
        $fonction = new Service();
       
        $form = $this->createForm(ServiceType::class, $fonction);
        $form->handleRequest($request);
        
        if($request->isXmlHttpRequest() && $fonction->getName()){

           $this->manager->persist($fonction);
           $this->manager->flush();

           $fonctions = $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC']);
           $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
           $row = '';

           foreach($fonctions as $key => $fonction){
            $option = "";
            foreach($departments as $department){
                if($department->getId() === $fonction->getDepartment()->getId()){
                    $option .= "<option value=".$department->getId()." selected>".$department->getName()."</option>";
                }else{
                    $option .= "<option value=".$department->getId().">".$department->getName()."</option>";
                }
            }
           $row .= '
           <tr id="tr-'.$fonction->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$fonction->getDepartment()->getName().'</td>
           <td id="td-'.$fonction->getId().'">'.$fonction->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$fonction->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$fonction->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$fonction->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$fonction->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$fonction->getName().'? Toutes les données liées à cette fonction seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$fonction->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$fonction->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier un département</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$fonction->getId().'" action="">
                               <div class="row">
                                    <div class="col-md-6 col-sm-6 col-xs-6">
                                    <div class="form-group">
                                        <label for="department">Département</label>
                                        <select name="department" class="form-control">
                                            '. $option .'
                                        </select>
                                    </div>
                                </div>
                                   <div class="col-md-6 col-sm-16col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$fonction->getId().'" class="form-control" value="'.$fonction->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="fonction" value="'.$fonction->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$fonction->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }
           return new JsonResponse(['fonction' => $fonction->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest() && $request->get('fonction')){
  
            $fonction = $this->manager->getRepository(Service::class)->find($request->get('fonction'));
            $department = $this->manager->getRepository(Department::class)->find($request->get('department'));

            
            $fonction->setName($request->get('name'));
            $fonction->setDepartment($department);
            
            
            $this->manager->persist($department);
            $this->manager->flush();


            $fonctions = $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC']);
            $departments = $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC']);
            
           $row = '';
           foreach($fonctions as $key => $fonction){
            $option = "";
            foreach($departments as $department){
                if($department->getId() === $fonction->getDepartment()->getId()){
                    $option .= "<option value=".$department->getId()." selected>".$department->getName()."</option>";
                }else{
                    $option .= "<option value=".$department->getId().">".$department->getName()."</option>";
                }
            }
           $row .= '
           
           <tr id="tr-'.$fonction->getId().'">
           <td>'.($key+1).'</td>
           <td>'.$fonction->getDepartment()->getName().'</td>
           <td id="td-'.$fonction->getId().'">'.$fonction->getName().'</td>
           <td >
               <a type="submit" id="btn-modify-'.$fonction->getId().'"
                   class="btn btn-success btn-sm">Modifier <i class="fa fa-edit"></i></a>
               <a type="submit" id="delete-'.$fonction->getId().'" class="btn btn-danger btn-sm"  data-toggle="modal"
                   data-target="#modal-danger">Supprimer 
                   <i class="fa fa-trash"></i></a>
                   <div class="modal fade" id="modal_delete_'.$fonction->getId().'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog" role="document">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title text-uppercase" style="color:#ffff;" >'.$fonction->getName().'</h5>
                               <a href="#" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </a>
                           </div>
                           <div class="modal-body">
                               <p>Voulez-vous vraiment supprimer '.$fonction->getName().'? Toutes les données liées à cette fonction seront définitivement supprimées!</p>
                           </div>
                           <div class="modal-footer">
                               <a href="#" class="btn btn-secondary" data-dismiss="modal" style="color:#fff;">Annuler</a>
                               <a id="btn-delete-'.$fonction->getId().'" class="btn btn-danger" style="color:#fff;">Supprimer</a>
                           </div>
                       </div>
                   </div>
               </div>
               <div id="modal_edit_'.$fonction->getId().'" class="modal fade" id="form" tabindex="-1" role="dialog"
                   aria-labelledby="exampleModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                       <div class="modal-content">
                           <div class="modal-header border-bottom-0">
                               <h5 class="modal-title text-center" id="exampleModalLabel">Modifier un département</h5>
                               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                   <span aria-hidden="true">&times;</span>
                               </button>
                           </div>
                           
                           <div class="modal-body">
                           <form id="edit_form_'.$fonction->getId().'" action="">
                               <div class="row">
                                  <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="department">Département</label>
                                           <select name="department" class="form-control">
                                            '. $option .'
                                           </select>
                                       </div>
                                   </div>
                                   <div class="col-md-6 col-sm-6 col-xs-6">
                                       <div class="form-group">
                                           <label for="name">Nom</label>
                                           <input type="text" name="name" id="name-'.$fonction->getId().'" class="form-control" value="'.$department->getName().'">
                                       </div>
                                   </div>
                               </div>
                               <input type="hidden" name="fonction" value="'.$fonction->getId().'">
                            </form>
                           </div>
                           <div class="modal-footer border-top-0 d-flex justify-content-center">
                               <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                               <button type="submit" id="edit-btn-'.$fonction->getId().'" class="btn btn-warning">Modifier</button>
                           </div>
                       </div>
                   </div>
               </div>
           </td>
            </tr>
           ';
           }

          
           return new JsonResponse(['fonction' => $fonction->getId(), 'row' => $row]);
        }elseif($request->isXmlHttpRequest()){
       
        }

        
        return $this->render('backend/fonctions/index.html.twig', [
            'fonctions' => $this->manager->getRepository(Service::class)->findBy([], ['id' => 'DESC']),
            'form' => $form->createView(),
            'departments' => $this->manager->getRepository(Department::class)->findBy([], ['id' => 'DESC'])
        ]);
    }

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/fonctions/delete", name="fonction_remove")
     */
    public function fonctionRemove(Request $request)
    {   
        if($request->isXmlHttpRequest() && $request->get('fonction')){
            $fonction = $this->manager->getRepository(Service::class)->find($request->get('fonction'));
            
            if($fonction->getId()){

                $id = $fonction->getId();

                $this->manager->remove($fonction);
                $this->manager->flush();

              return new JsonResponse(['fonction' => $id]);

            }

        }

        return null;
    }

    
    /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     * 
     * @param Request $request
     * @return JsonResponse
     * @Route("/get-units-from-entity", name="get_units_from_entity")
     */
    public function listUnitOfEntity(Request $request)
    {
        dd($request);
        // Get Entity manager and repository
        $unitRepo = $this->manager->getRepository(Unit::class);
        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $units = $unitRepo->createQueryBuilder("u")
            ->where("u.entity = :entityId")
            ->setParameter("entityId", $request->get("entityId"))
            ->getQuery()
            ->getResult();
        
        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($units as $unit){
            $responseArray[] = array(
                "id" => $unit->getId(),
                "name" => $unit->getName()
            );
        }
        
        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }

      /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     * 
     * @param Request $request
     * @return JsonResponse
     * @Route("/get-directions-from-unit", name="get_directions_from_unit")
     */
    public function listDirectionsOfUnit(Request $request)
    {
        // Get Entity manager and repository
        $directionRepo = $this->manager->getRepository(Direction::class);
        
        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $directions = $directionRepo->createQueryBuilder("d")
            ->where("d.unit = :unitId")
            ->setParameter("unitId", $request->get("unitId"))
            ->getQuery()
            ->getResult();
        
        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($directions as $direction){
            $responseArray[] = array(
                "id" => $direction->getId(),
                "name" => $direction->getName()
            );
        }
        
        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }

      /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     * 
     * @param Request $request
     * @return JsonResponse
     * @Route("/get-departments-from-direction", name="get_departments_from_direction")
     */
    public function listDepartmentOfDirection(Request $request)
    {
        // Get Entity manager and repository
        $departmentRepo = $this->manager->getRepository(Department::class);
        
        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $departments = $departmentRepo->createQueryBuilder("d")
            ->where("d.direction = :directionId")
            ->setParameter("directionId", $request->get("directionId"))
            ->getQuery()
            ->getResult();
        
        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($departments as $department){
            $responseArray[] = array(
                "id" => $department->getId(),
                "name" => $department->getName()
            );
        }
        
        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }

     /**
     * Returns a JSON string with the neighborhoods of the City with the providen id.
     * 
     * @param Request $request
     * @return JsonResponse
     * @Route("/get-services-from-department", name="get_services_from_department")
     */
    public function listServicesOfDepartment(Request $request)
    {
        // Get Entity manager and repository
        $serviceRepo = $this->manager->getRepository(Service::class);
        
        // Search the neighborhoods that belongs to the city with the given id as GET parameter "cityid"
        $services = $serviceRepo->createQueryBuilder("s")
            ->where("s.department = :departmentId")
            ->setParameter("departmentId", $request->get("departmentId"))
            ->getQuery()
            ->getResult();
        
        // Serialize into an array the data that we need, in this case only name and id
        // Note: you can use a serializer as well, for explanation purposes, we'll do it manually
        $responseArray = array();
        foreach($services as $service){
            $responseArray[] = array(
                "id" => $service->getId(),
                "name" => $service->getName()
            );
        }
        
        // Return array with structure of the neighborhoods of the providen city id
        return new JsonResponse($responseArray);

        // e.g
        // [{"id":"3","name":"Treasure Island"},{"id":"4","name":"Presidio of San Francisco"}]
    }
}
