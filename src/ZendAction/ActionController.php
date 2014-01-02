<?php
/**
 * 
 *
 * @author Dipak Yadav <dipak dot kumar at clavax dot com>
 * 
 * 
 */

namespace ZendAction;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class ActionController extends AbstractActionController
{
    /**
     * @var array
     * key => value pairs of 'route-action-name' => 'action/file/path'
     */
    protected $actions = array();
    
    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Exception\DomainException
     */
    public function onDispatch(MvcEvent $e)
    {
        
        $routeMatch = $e->getRouteMatch();
        
        if (!$routeMatch) {
            /**
             * @todo Determine requirements for when route match is missing.
             *       Potentially allow pulling directly from request metadata?
             */
            throw new Exception\DomainException('Missing route matches; unsure how to retrieve action');
        }

        $action = $routeMatch->getParam('action', 'not-found');
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            
            // Check if action file exist.
            if(array_key_exists($action, $this->actions)) {
                
                $actionObj = new $this->actions[$action]();
                $actionObj->controller = $this;
                $actionResponse = $actionObj->indexAction();
                $e->setResult($actionResponse);
                return $actionResponse;
            } else {
            
                $method = 'notFoundAction';
            }
        }

        $actionResponse = $this->$method();

        $e->setResult($actionResponse);

        return $actionResponse;
    }
}