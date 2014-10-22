<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class AdminController extends BaseController 
{
	public function indexAction (Request $request)
    {
        $fields = $request->query->all();
        $conditions = array(
            'roles'=>'ROLE_SUPER_ADMIN',
            'truename'=>'',
            'number'=>''
        );

        if(isset($fields['search_truename'])){
            $conditions['truename']=$fields['search_truename'];
            $conditions['number']=$fields['search_number'];
        }
        $paginator = new Paginator(
            $this->get('request'),
            $this->getUserService()->searchUserCount($conditions),
            20
        );

        $users = $this->getUserService()->searchUsers(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaAdminBundle:Admin:index.html.twig', array(
            'users' => $users ,
            'paginator' => $paginator
        ));
    }

    public function addAdminRoleAction(Request $request,$id)
    {
        $user=$this->getUserService()->getUser($id);
        if(empty($user)){
            throw $this->createNotFoundException("用户不存在");            
        }

        if(in_array('ROLE_SUPER_ADMIN', $user['roles'])){
            throw $this->createAccessDeniedException("用户已经是超级管理员");
        }
        $user['roles'][]='ROLE_SUPER_ADMIN';
        $this->getUserService()->changeUserRoles($user['id'], $user['roles']);
        return $this->render('TopxiaAdminBundle:Teacher:teacher-table-tr.html.twig', array(
            'user' => $user,
        ));
    }

    public function cancleAdminRoleAction(Request $request,$id)
    {
        $user=$this->getUserService()->getUser($id);
        if(empty($user)){
            throw $this->createNotFoundException("用户不存在");            
        }

        if(!in_array('ROLE_SUPER_ADMIN', $user['roles'])){
            throw $this->createAccessDeniedException("用户不是超级管理员");
        }
        $user['roles']=array_diff($user['roles'],array('ROLE_SUPER_ADMIN','ROLE_ADMIN')); 
        $this->getUserService()->changeUserRoles($user['id'], $user['roles']);
        return $this->render('TopxiaAdminBundle:Teacher:teacher-table-tr.html.twig', array(
            'user' => $user,
        ));
    }
}