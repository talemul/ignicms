<?php

namespace App\Models;

use Despark\Cms\Admin\Traits\AdminImage;
use Despark\Cms\Admin\Traits\AdminModelTrait;
use Despark\Cms\Admin\Traits\ModelWithSlug;
use Despark\Cms\Models\AdminModel;

class SeoPage extends AdminModel
{
    use AdminImage, ModelWithSlug, AdminModelTrait;

    protected $sluggable = [
        'build_from' => 'page_title',
        'save_to' => 'page_slug',
    ];

    protected $table = 'seo_pages';

    protected $fillable = [
        'page_title',
        'page_slug',
        'meta_title',
        'meta_description',
        'meta_image',
    ];

    protected $rules = [
        'page_title' => 'required|string|max:70',
        'page_slug' => 'string|max:70',
        'meta_title' => 'required|string|max:70',
        'meta_description' => 'required|string|max:160',
        'meta_image' => 'required|image|max:5000',
    ];

    protected $rulesUpdate = [
        'page_slug' => 'required|string|max:70',
        'meta_image' => 'image|max:5000',
    ];

    public $identifier = 'seo_page';

    public function getRulesUpdate()
    {
        return array_merge($this->rules, $this->rulesUpdate);
    }
}
