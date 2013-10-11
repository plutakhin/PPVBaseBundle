<?php
namespace PPV\BaseBundle\Annotations\User;

/**
 * UserAccessInterface
 *
 * @author Pavel Plutakhin <pavel.plutakhin@simbirsoft.com>
 */
interface UserAccessInterface
{
    /**
     * Checks if the user has a list of roles.
     *
     * @return boolean
     */
    public function hasRoles(array $roles);

    /**
     * Checks if the user has a list of permissions
     *
     * @return boolean
     */
    public function hasPermissions(array $permissions);
}
