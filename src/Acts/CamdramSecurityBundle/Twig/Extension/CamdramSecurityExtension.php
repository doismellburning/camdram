<?php
namespace Acts\CamdramSecurityBundle\Twig\Extension;

use Acts\CamdramSecurityBundle\Security\SecurityUtils;

class CamdramSecurityExtension extends \Twig_Extension
{
    /**
     * @var SecurityUtils
     */
    protected $utils;

    /**
     * @param OAuthHelper $helper
     */
    public function __construct(SecurityUtils $utils)
    {
        $this->utils = $utils;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'camdram_security_services' => new \Twig_Function_Method($this, 'getServices'),
            'acl_entries' => new \Twig_Function_Method($this, 'getAclEntries'),
            'is_granted' => new \Twig_Function_Method($this, 'isGranted'),
        );
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->utils->getServices();
    }

    public function getAclEntries($class, $role, $mask)
    {
        return $this->utils->getAclEntries($class, $role, $mask);
    }

    public function isGranted($attributes, $object)
    {
        return $this->utils->isGranted($attributes, $object);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'camdram_security';
    }
}