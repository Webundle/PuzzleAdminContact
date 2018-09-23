<?php
namespace Puzzle\Admin\ContactBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Puzzle\Admin\ContactBundle\Form\Type\ContactCreateType;
use Puzzle\Admin\ContactBundle\Form\Type\ContactUpdateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use GuzzleHttp\Exception\BadResponseException;
use Puzzle\ConnectBundle\ApiEvents;
use Puzzle\ConnectBundle\Event\ApiResponseEvent;
use Puzzle\ConnectBundle\Service\PuzzleApiObjectManager;
use Puzzle\ConnectBundle\Service\ErrorFactory;

/**
 *
 * @author AGNES Gnagne Cedric <cecenho55@gmail.com>
 *
 */
class ContactController extends Controller
{
    /**
     * @var array $fields
     */
    private $fields;
    
    public function __construct() {
        $this->fields = ['firstName', 'lastName', 'email', 'phone', 'company', 'position', 'location'];
    }
    
    /***
     * List contacts
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function listAction(Request $request) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $contacts = $apiClient->pull('/contact/contacts');
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $contacts = [];
        }
        
        return $this->render("PuzzleAdminContactBundle:Contact:list.html.twig",['contacts' => $contacts]);
    }
    
    /***
     * Create a new contact
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function createAction(Request $request) {
        $data = PuzzleApiObjectManager::hydratate($this->fields, []);
        
        $form = $this->createForm(ContactCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_contact_create') 
        ]);
        
        if ($groupId = $request->query->get('group')) {
            $data['group'] = $groupId;
            $form->add('group', HiddenType::class, ['data' => $groupId]);
        }
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
            //** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
                $apiClient = $this->get('puzzle_connect.api_client');
                $contact = $apiClient->push('post', '/contact/contacts', $postData);
                $this->addFlash('success', $this->get('translator')->trans('message.post', [], 'success'));
                
                return $this->redirectToRoute('admin_contact_update', array('id' => $contact['id']));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminContactBundle:Contact:create.html.twig", ['form' => $form->createView()]);
    }
    
    /***
     * Show contact
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function showAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $contact = $apiClient->pull('/contact/contacts/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $contact = [];
        }
        
        return $this->render("PuzzleAdminContactBundle:Contact:show.html.twig", array('contact' => $contact));
    }
    
    /***
     * Update contact
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function updateAction(Request $request, $id) {
        /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        
        try {
            $contact = $apiClient->pull('/contact/contacts/'.$id);
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            $contact = [];
        }
        
        $data = PuzzleApiObjectManager::hydratate($this->fields, $contact);
        
        /** @var Puzzle\ConectBundle\Service\PuzzleAPIClient $apiClient */
        $apiClient = $this->get('puzzle_connect.api_client');
        
        $form = $this->createForm(ContactCreateType::class, $data, [
            'method' => 'POST',
            'action' => $this->generateUrl('admin_contact_update', ['id' => $id])
        ]);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() === true && $form->isValid() === true) {
             //** @var Puzzle\Admin\MediaBundle\Service\UploadManager $uploader */
            $uploader = $this->get('admin.media.upload_manager');
            $uploads = $uploader->prepareUpload($_FILES, $request->getSchemeAndHttpHost());
            
            $postData = $form->getData();
            $postData['picture'] = $uploads && count($uploads) > 0 ? $uploads[0] : $postData['file-url'] ?? null;
            $postData = PuzzleApiObjectManager::sanitize($postData);
            
            try {
                $contact = $apiClient->push('put', '/contact/contacts/'.$id, $postData);
                $this->addFlash('success', $this->get('translator')->trans('message.put', [], 'success'));
                
                return $this->redirectToRoute('admin_contact_update', array('id' => $id));
            }catch (BadResponseException $e) {
                /** @var EventDispatcher $dispatcher */
                $dispatcher = $this->get('event_dispatcher');
                $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
                
                $form = ErrorFactory::createFormError($form, $e);
            }
        }
        
        return $this->render("PuzzleAdminContactBundle:Contact:update.html.twig", [
            'contact' => $contact,
            'form' => $form->createView()
        ]);
    }
    
    /***
     * Delete a contact
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @Security("has_role('ROLE_CONTACT') or has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, $id) {
        try {
            /** @var Puzzle\ConnectBundle\Service\PuzzleAPIClient $apiClient */
            $apiClient = $this->get('puzzle_connect.api_client');
            $apiClient->push('delete', '/contact/contacts/'.$id);
            
            if ($request->isXmlHttpRequest() === true) {
                return new JsonResponse(['status' => true]);
            }
            
            $this->addFlash('success', $this->get('translator')->trans('message.delete', [], 'success'));
        }catch (BadResponseException $e) {
            /** @var EventDispatcher $dispatcher */
            $dispatcher = $this->get('event_dispatcher');
            $event  = $dispatcher->dispatch(ApiEvents::API_BAD_RESPONSE, new ApiResponseEvent($e, $request));
            
            if ($request->isXmlHttpRequest()) {
                return $event->getResponse();
            }
        }
        
        return $this->redirectToRoute('admin_contact_list');
    }
}
