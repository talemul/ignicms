<?php

namespace Despark\Cms\Admin\Traits;

use Despark\Cms\Admin\Helpers\FormBuilder;
use App\Models\I18n;
use Illuminate\Support\Facades\Request;

/**
 * Class AdminConfigTrait.
 */
trait AdminConfigTrait
{
    /**
     * @array $adminColumns table columns in admin list page
     */
    public $adminColumns;

    /**
     * @array $filters filters at list page
     */
    public $adminFilters;

    /**
     * @var
     */
    public $adminFormFields;

    // Preview button show/hide
    /**
     * @var bool
     */
    public $adminPreviewMode = false;

    /**
     * @var string
     */
    public $adminPreviewUrlParams = [];

    /**
     * @return mixed
     */
    public function adminTableColumns()
    {
        return $this->adminColumns;
    }

    /**
     * Transform 1/0 or true/false into yes/no.
     *
     * @param $data
     *
     * @return string
     */
    public function yes_no($data)
    {
        return $data ? 'yes' : 'no';
    }

    /**
     * Format date - F jS, Y.
     *
     * @param $data
     *
     * @return string
     */
    public function formatDefaultData($data)
    {
        return date('F jS, Y', strtotime($data));
    }

    /**
     * return model fields in proper way.
     *
     * @param $record
     * @param $col
     *
     * @return mixed
     */
    public function renderTableRow($record, $col)
    {
        switch (array_get($col, 'type', 'text')) {
            case 'yes_no':
                return $record->yes_no($record->{$col['db_field']});
                break;
            case 'format_default_date':
                return $record->formatDefaultData($record->{$col['db_field']});
                break;
            case 'sort':
                return '<div class="fa fa-sort sortable-handle"></div>';
                break;
            case 'relation':
                return $record->{$col['relation']}->{$col['db_field']};
                break;
            case 'translation':
                $locale = config('app.locale', 'en');
                $i18n = I18n::select('id')->where('locale', $locale)->first();
                if ($i18n) {
                    $i18nId = $i18n->id;

                    return $record->translate($i18nId)->{$col['db_field']};
                }

                return 'No translation';
                break;
            default :
                return $record->{$col['db_field']};
                break;
        }
    }

    /**
     * combine all seach and filter functions into filtering.
     *
     * @return $this
     */
    public function filtering()
    {
        return $this->searchText();
    }

    /**
     * create query for list page.
     */
    public function searchText()
    {
        $query = $this->newQuery();
        if (Request::get('admin_text_search')) {
            foreach ($this->adminFilters['text_search']['db_fields'] as $field) {
                $query->orWhere($field, 'LIKE', '%'.Request::get('admin_text_search').'%');
            }
        }

        return $query;
    }

    /**
     * @return mixed
     */
    public function hasFilters()
    {
        return $this->adminFilters;
    }

    /**
     * @return bool
     */
    public function hasSearchTextFilter()
    {
        return $this->adminFilters['text_search'] and $this->adminFilters['text_search']['db_fields'];
    }

    /**
     * @return string
     */
    public function buildForm()
    {
        $formFields = '';

        foreach ($this->getFormFields() as $field => $options) {
            $formBuilder = new FormBuilder();
            $formFields .= $formBuilder->field($this, $field, $options);
        }

        return $formFields;
    }

    /**
     * @return mixed
     */
    public function getFormFields()
    {
        return $this->adminSetFormFields()->adminFormFields;
    }

    /**
     * @return mixed
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @return mixed
     */
    public function getRulesUpdate()
    {
        return (isset($this->rulesUpdate)) ? $this->rulesUpdate : $this->rules;
    }

    /**
     * Generate preview button for the CMS
     * $adminPreviewMode should be true.
     *
     *@return string
     */
    public function adminPreviewButton()
    {
        if ($this->adminPreviewMode and $this->exists) {
            $db_field = $this->adminPreviewUrlParams['db_field'];

            return \Html::link(
                route($this->adminPreviewUrlParams['route'], [$this->$db_field, 'preview_mode=1']),
                'Preview',
                ['class' => 'btn btn-primary', 'target' => '_blank']
            );
        }
    }
}
