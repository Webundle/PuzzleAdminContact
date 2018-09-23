<?php
namespace Puzzle\Admin\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ContactBundle\Form\Type\GroupCreateType;
use Puzzle\Admin\ContactBundle\Form\Type\GroupUpdateType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;

/**
 * 
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class GroupController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['name', 'description', 'parent'];
    }
    
	/***
	 * List groups
	 * 
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
	 */
    public function listAction(Request $request, $current = "NULL") {
        try {
    		$criteria = [];
    		$criteria['filter'] = 'parent=='.$current;
    		
    		/** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
    		$apiClient = $this->get('puzzle_connect.api_client');
    		$groups = $apiClient->pull('/contact/groups', $criteria);
    		$currentGroup = $current != "NULL" ? $apiClient->pull('/contact/groups/'.$current) : null;
    		
    		if ($currentGroup && isset($currentGroup['_embedded']) && isset($currentGroup['_embedded']['parent'])) {
    		    $parent = $currentGroup['_embedded']['parent'];
    		}else {
    		    $parent = null;
    		}
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $groups = $currentGroup = $parent = [];
        }
		
		return $this->render("PuzzleAdminContactBundle:Group:list.html.twig",[
		    'groups'          => $groups,
		    'currentGroup'    => $currentGroup,
		    'parent'          => $parent
		]);
	}
	
    /***
     * Create a new group
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $parentId = $request->query->get('parent');
        $data = PuzzleApiObjectManager::hydratate($this->fields, ['parent' => $parentId]);
        
        $form = $this->createForm(GroupCreateType::class, $data, [
            'method' => 'POST',
            'action' => !$parentId ? $this->generateUrl('admin_contact_group_create') : 
                                     $this->generateUrl('admin_contact_group_create', ['parent' => $parentId])
        ]);
        $form->handleRequest($request);
            
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $postData = $form->getData();
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('post', '/contact/groups', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                if ($request->isXmlHttpRequest() === true) {
                    return $event->getResponse();
                }
            }
            
            if ($parentId !== null) {
                return $this->redirectToRoute('admin_contact_group_show', array('id' => $parentId));
            }
            
            return $this->redirectToRoute('admin_contact_group_list');
        }
        
        return $this->render("PuzzleAdminContactBundle:Group:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $group = $apiClient->pull('/contact/groups/'.$id);
            
            if (isset($group['contacts']) && count($group['contacts']) > 0){
                $criteria = [];
                $criteria['filter'] = 'id=:'.implode(';', $group['contacts']);
                
                $contacts = $apiClient->pull('/contact/contacts', $criteria);
            }else {
                $contacts = null;
            }
            
            $parent = null;
            if (isset($group['_embedded'])) {
                $parent = $group['_embedded']['parent'] ?? null;
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $group = $parent = $contacts = [];
        }
        
        return $this->render("PuzzleAdminContactBundle:Group:show.html.twig", array(
            'group' => $group,
            'contacts' => $contacts,
            'parent' => $parent
        ));
    }
    
    /***
     * Update group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        $parentId = $request->query->get('parent');
        /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        
        try {
            $group = $apiClient->pull('/contact/groups/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $group = [];
        }
        
        $data = PuzzleApiObjectManager::hydratate($this->fields, $group);
        $data['parent'] = $parentId;
        
        $form = $this->createForm(GroupUpdateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_contact_group_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            $postData = $form->getData();
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $apiClient->push('post', '/contact/groups', $postData);
                
                if ($request->isXmlHttpRequest() === true) {
                    return new JsonResponse(true);
                }
                
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                if ($request->isXmlHttpRequest() === true) {
                    return $event->getResponse();
                }
            }
            
            if ($parentId !== null) {
                return $this->redirectToRoute('admin_contact_group_show', array('id' => $parentId));
            }
            
            return $this->redirectToRoute('admin_contact_group_list');
        }
        
        return $this->render("PuzzleAdminContactBundle:Group:update.html.twig", [
            'group' => $group,
            'form' => $form->createView()
        ]);
    }
    
    /**
     * Add contact
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addContactAction(Request $request, $id, $item) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('put', '/contact/groups/'.$id, ['contacts_to_add' => [$item]]);
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($response['message'] ?? null, $response['code']);
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            if ($request->isXmlHttpRequest() === true) {
                return $event->getResponse();
            }
        }
        
        return $this->redirect($this->generateUrl('admin_contact_group_list'));
    }
    
    /**
     * Remove contact
     * 
     * @param Request $request
     * @param string $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function removeContactAction(Request $request, $id, $item) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $response = $apiClient->push('put', '/contact/groups/'.$id, ['contacts_to_remove' => [$item]]);
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse($response['message'] ?? null, $response['code']);
            }
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            if ($request->isXmlHttpRequest() === true) {
                return $event->getResponse();
            }
        }
        
        return $this->redirect($this->generateUrl('admin_contact_group_list'));
    }
    
    /***
     * Delete group
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $group = $apiClient->pull('/contact/groups/'.$id);
            $parentId = $group['_embedded']['parent']['id'] ?? null;
            
            if ($parentId){
                $route = $this->redirectToRoute('admin_contact_group_show', array('id' => $parentId));
        	}else{
        		$route = $this->redirectToRoute('admin_contact_group_list');
        	}
        	
        	$response = $apiClient->push('delete', '/contact/groups/'.$id);
        	if ($request->isXmlHttpRequest()) {
        	    return new JsonResponse($response);
        	}
        	
        	$this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
        	
        	return $route;
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            
            if ($request->isXmlHttpRequest()) {
                return $event->getResponse();
            }
            
            return $this->redirectToRoute('admin_contact_group_list');
        }
    }
}
