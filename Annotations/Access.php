<?php
namespace PPV\BaseBundle\Annotations;

/**
 * Класс аннотации хранящий список ролей и прав доступа
 * 
 * @Annotation
 * @Access(roles="", perms="")
 * @author Pavel Plutakhin <pavel.plutakhin@simbirsoft.com>
 */
class Access
{
    public $roles;
    public $perms;

    public function getRoles()
    {
        return $this->_implodeParams($this->roles);
    }

    public function getPerms()
    {
        return $this->_implodeParams($this->perms);
    }

    /**
     * Форматирует аннотации к массиву
     * @param string||array $params
     * @return array
     */
    private function _implodeParams($params)
    {
        return is_array($params)
            ? $params
            : ((empty($params))
                ? array()
                : array_map('trim', explode(',', $params))
            );
    }
}