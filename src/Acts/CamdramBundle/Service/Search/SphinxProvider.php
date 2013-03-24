<?php
namespace Acts\CamdramBundle\Service\Search;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Pagerfanta\PagerfantaInterface;
use Foolz\SphinxQL\SphinxQL;

class  SphinxProvider implements ProviderInterface
{
    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $repository
     * @param $query
     * @param $limit
     * @param $offset
     * @return array
     */
    public function executeAutocomplete($repository, $q, $limit)
    {
        if (empty($q)) return array();

        $finder = $this->container->get('acts.sphinx_realtime.finder.'.$repository);

        $query = SphinxQL::forge()->select()->match('name', $q.'*');

        $results = $finder->find($query, $limit);

        return $results;
    }

    /**
     * @param $repository
     * @param $query
     * @param $limit
     * @param $offset
     * @return \Pagerfanta\PagerfantaInterface;
     */
    public function executeTextSearch($repository, $q)
    {
        $finder = $this->container->get('acts.sphinx_realtime.finder.'.$repository);
        $query = SphinxQL::forge()->select()->match('(name,description)', $q);
        return $finder->findPaginated($query);
    }
}