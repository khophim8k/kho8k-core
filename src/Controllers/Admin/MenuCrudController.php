<?php

namespace Kho8k\Core\Controllers\Admin;

use Kho8k\CoreHttp\Requests\MenuRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Kho8k\Core\Models\Menu;
use Kho8k\Core\Models\Category;
use Kho8k\Core\Models\Region;

/**
 * Class MenuCrudController
 * @package Kho8k\CoreHttp\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MenuCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ReorderOperation;

    use \Kho8k\Core\Traits\Operations\BulkDeleteOperation {
        bulkDelete as traitBulkDelete;
    }

    public function setup()
    {
        $this->crud->setModel(Menu::class);
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/menu');
        $this->crud->setEntityNameStrings('menu item', 'menu items');
        $this->crud->enableReorder('name', 2);
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->authorize('browse', Menu::class);

        $this->crud->addColumn([
            'name' => 'name',
            'label' => 'Label',
        ]);
        $this->crud->addColumn([
            'label' => 'Parent',
            'type' => 'select',
            'name' => 'parent_id',
            'entity' => 'parent',
            'attribute' => 'name',
            'model' => Menu::class,
        ]);
        $this->crud->addColumn([
            'name' => 'link',
            'label' => 'Link',
            'type' => 'url',
        ]);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $this->authorize('create', Menu::class);

        $this->crud->addField([
            'name' => 'name',
            'label' => 'Label',
        ]);
        $this->crud->addField([
            'label' => 'Parent',
            'type' => 'select',
            'name' => 'parent_id',
            'entity' => 'parent',
            'attribute' => 'name',
            Menu::class
        ]);

        // Thêm trường chọn kiểu loại liên kết
        $this->crud->addField([
            'name' => 'category',
            'label' => 'Select Category',
            'type' => 'select_from_array',
            'options' => ['' => '--- Select ---'] + Category::pluck('name', 'slug')->toArray(), // Thêm tùy chọn rỗng
            'allows_null' => true,
            'default' => '',
        ]);
        $this->crud->addField([
            'name' => 'countrys',
            'label' => 'Select Countrys',
            'type' => 'select_from_array',
            'options' => ['' => '--- Select ---'] +Region::pluck('name', 'slug')->toArray(), // Tạo danh sách với slug làm value, name làm label
            'allows_null' => true,
            'default' => '',
        ]);
        $this->crud->addField([
            'name' => ['type', 'link', 'internal_link'],
            'label' => 'Type',
            'type' => 'page_or_link',
        ]);
        // Thêm trường chọn thể loại hoặc quốc gia


        // Script xử lý tự động internal_link dựa vào lựa chọn của người dùng
        $this->crud->addField([
            'name' => 'custom_script',
            'type' => 'custom_html',
            'value' => '<script>
            document.querySelector(".external_link input").disabled=true;
             var hiddenInput = document.getElementsByName("link")[0];

            document.querySelector("select[name=\'category\']").addEventListener("change", function() {
            var selectedValue = this.value;
            var countrySelect = document.querySelector("select[name=\'countrys\']");
            var internalLinkInput = document.querySelector(".internal_link input");

            // Đặt countrySelect về mặc định nếu category được chọn
            if (selectedValue) {
                countrySelect.selectedIndex = 0; // Chọn tùy chọn rỗng
                internalLinkInput.value =  "/the-loai/" +selectedValue;
                hiddenInput.value=  "/the-loai/" +selectedValue;
            }
        });

        document.querySelector("select[name=\'countrys\']").addEventListener("change", function() {
            var selectedValue = this.value;
            var categorySelect = document.querySelector("select[name=\'category\']");
            var internalLinkInput = document.querySelector(".internal_link input");

            // Đặt categorySelect về mặc định nếu country được chọn
            if (selectedValue) {
                categorySelect.selectedIndex = 0; // Chọn tùy chọn rỗng
                internalLinkInput.value =  "/quoc-gia/"+selectedValue; // Cập nhật input với value của country
                  hiddenInput.value= "/quoc-gia/"+selectedValue;
            }
        });
        </script>'
        ]);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->authorize('update', $this->crud->getEntryWithLocale($this->crud->getCurrentEntryId()));


        $this->setupCreateOperation();
    }

    protected function setupDeleteOperation()
    {
        $this->authorize('delete', $this->crud->getEntryWithLocale($this->crud->getCurrentEntryId()));
    }

    public function bulkDelete()
    {
        $this->crud->hasAccessOrFail('bulkDelete');
        $entries = request()->input('entries', []);
        $deletedEntries = [];
        foreach ($entries as $key => $id) {
            if ($entry = $this->crud->model->find($id)) {
                $deletedEntries[] = $entry->delete();
            }
        }

        return $deletedEntries;
    }
}
