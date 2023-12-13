<?php

namespace App\Data;

use App\Entity\Category;

class SearchDataArticle
{

    /**
     * @var string
     */
    public $q = '';

    /**
     * @var Category[]
     */
    public $category = null;

}
