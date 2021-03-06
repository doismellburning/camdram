<?php

namespace Acts\CamdramBundle\Search;

interface SearchableInterface
{

    public function getId();

    public function getName();

    public function getShortName();

    public function getDescription();

    public function getSlug();

    public function getRank();
}
