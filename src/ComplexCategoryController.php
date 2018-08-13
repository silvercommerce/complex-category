<?php

namespace SilverCommerce\ComplexCategory;

use CategoryController;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\DropdownField;

class ComplexCategoryController extends CategoryController
{
    const DEFAULT_SORT = "Default";

    const DEFAULT_LIMIT = 15;

    /**
     * Specify sort options and their titles
     * (these can be expanded upon using global config)
     * 
     * @var array
     */
    private static $sort_options = [
        self::DEFAULT_SORT => self::DEFAULT_SORT,
        'Title ASC' => 'Name (A-Z)',
        'Title DESC' => 'Name (Z-A)',
        'BasePrice ASC' => 'Price (Low - High)',
        'BasePrice DESC' => 'Price (High - Low)'
    ];

    /**
     * List of default limit options (can be amended via config)
     * 
     * @var array
     */
    private static $show_options = [
        self::DEFAULT_LIMIT => self::DEFAULT_LIMIT,
        '30' => '30',
        '60' => '60',
        '90' => '90'
    ];

    /**
     * Get an i18n friendly version of the sort name
     * 
     * @return string
     */
    public function getTranslatedSort($option)
    {
        return _t(self::class . "." . $option, $option);
    }

    /**
     * Generate a sort/limit form
     * 
     * @return Form 
     */
    public function SortLimitForm()
    {
        $sort_options = $this->config()->sort_options;
        $show_options = $this->config()->show_options;

        // Get the current query
        $query = $this->getQuery();

        // Create a new array based on available sort/limit options
        $sort = [];
        $limit = [];

        $i = 0;
        foreach ($sort_options as $key => $value) {
            $sort[$i] = $this->getTranslatedSort($value);
            $i++;
        }

        $i = 0;
        foreach ($show_options as $item) {
            $limit[$i] = $item;
            $i++;
        }

        $form = Form::create(
            $this,
            __FUNCTION__,
            FieldList::create(
                $sort_field = DropdownField::create(
                    "s[sort]",
                    _t(self::class . '.Sort', 'Sort'),
                    $sort
                ),
                $limit_field = DropdownField::create(
                    "s[show]",
                    _t(self::class . '.Show', 'Show'),
                    $limit
                ),
                FormAction::create(
                    "go",
                    _t(self::class . '.Go', 'Go')
                )
            ),
            FieldList::create()
        );

        // Add extra bootstrap class (if required)
        $form
            ->addExtraClass("form-inline")
            ->setTemplate('SilverCommerce\\ComplexCategory\\' . __FUNCTION__)
            ->setFormMethod("GET")
            ->setFormAction($this->Link())
            ->disableSecurityToken();

        if (!empty($query["sort"])) {
            $sort_field->setValue($query["sort"]);
        }

        if (!empty($query["show"])) {
            $limit_field->setValue($query["show"]);
        }

        $this->extend("updateSortLimitForm", $form);

        return $form;
    }

    /**
     * Get a paginated list of all products at this level and below
     * 
     * This is expanded to support the length dropdown
     *
     * @return PaginatedList
     */
    public function PaginatedAllProducts($limit = 10)
    {
        $show_options = Config::inst()->get(self::class, "show_options");

        $new_limit = $this->getCurrentOption($show_options, "show");

        if (!empty($new_limit)) {
            $limit = $new_limit; 
        } else {
            $limit = self::DEFAULT_LIMIT;
        }

        return parent::PaginatedAllProducts($limit);
    }
}