<?php

namespace Kho8k\Core\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Illuminate\Support\Facades\Route;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Kho8k\Core\Models\Theme;
use Prologue\Alerts\Facades\Alert;
use Illuminate\Support\Facades\Validator;

class ThemeManagementController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(Theme::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/theme');
        CRUD::setEntityNameStrings('theme', 'themes');
        $this->crud->denyAccess('update');
    }

    /**
     * Define which routes are needed for this operation.
     *
     * @param  string  $name  Name of the current entity (singular). Used as first URL segment.
     * @param  string  $routeName  Prefix of the route name.
     * @param  string  $controller  Name of the current CrudController.
     */
    protected function setupManagementRoutes($segment, $routeName, $controller)
    {
        Route::post($segment . '/{id}/active', [
            'as'        => $routeName . '.active',
            'uses'      => $controller . '@active',
            'operation' => 'update',
        ]);

        Route::post($segment . '/{id}/reset', [
            'as'        => $routeName . '.reset',
            'uses'      => $controller . '@reset',
            'operation' => 'update',
        ]);

        Route::post($segment . '/{id}/delete', [
            'as'        => $routeName . '.delete',
            'uses'      => $controller . '@delete',
            'operation' => 'update',
        ]);

    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */

        foreach (config('themes', []) as $key => $theme) {
            Theme::updateOrCreate([
                'name' => $key,
            ], [
                'display_name' => $theme['display_name'] ??  $theme['name'],
                'preview_image' => $theme['preview_image'] ?: '',
                'author' => $theme['author'] ?: '',
                'package_name' => $theme['package_name'],
            ]);
        }

        CRUD::addColumn(['name' => 'display_name', 'type' => 'text']);
        CRUD::addColumn(['name' => 'preview_image', 'type' => 'image']);
        CRUD::addColumn(['name' => 'version', 'type' => 'text']);
        $this->crud->addButtonFromModelFunction('line', 'editBtn', 'editBtn', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'resetBtn', 'resetBtn', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'activeBtn', 'activeBtn', 'beginning');
        $this->crud->addButtonFromModelFunction('line', 'deleteBtn', 'deleteBtn', '');
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $theme = $this->crud->getEntryWithLocale($this->crud->getCurrentEntryId());

        $fields = $theme->options;

        CRUD::addField(['name' => 'fields', 'type' => 'hidden', 'value' => collect($fields)->implode('name', ',')]);

        foreach ($fields as $field) {
            CRUD::addField($field);
        }
    }

    public function delete(Request $request, $id)
    {
        if (!backpack_user()->hasPermissionTo('Customize theme')) {
            abort(403);
        }
        $theme = Theme::fromCache()->find($id);
        if (is_null($theme)) {
            Alert::warning("Không tìm thấy dữ liệu giao diện")->flash();
            return redirect(backpack_url('theme'));
        }
        // delete row from db
        $theme->delete();

        Alert::success("Xóa giao diện thành công!")->flash();
        return redirect(backpack_url('theme'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        if (!backpack_user()->hasPermissionTo('Customize theme')) {
            abort(403);
        }

        $id = $this->crud->getCurrentEntryId() ?? $id;

        $this->data['entry'] = $this->crud->getEntryWithLocale($id);
        $this->crud->setOperationSetting('fields', $this->getUpdateFields());

        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit') . ' ' . $this->crud->entity_name;
        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }

    /**
     * Update the specified resource in the database.
     *
     * @return array|\Illuminate\Http\RedirectResponse
     */
    public function update()
    {
        if (!backpack_user()->hasPermissionTo('Customize theme')) {
            abort(403);
        }

        // execute the FormRequest authorization and validation, if one is required
        $request = $this->crud->validateRequest();

        // register any Model Events defined on fields
        $this->crud->registerFieldEvents();

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            [
                'value' => request()->only(explode(',', request('fields')))
            ]
        );

        $this->data['entry'] = $this->crud->entry = $item;

        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect(backpack_url('theme'));
    }

    /**
     * Get all fields needed for the EDIT ENTRY form.
     *
     * @param  int  $id  The id of the entry that is being edited.
     * @return array The fields with attributes, fake attributes and values.
     */
    public function getUpdateFields($id = false)
    {
        $fields = $this->crud->fields();
        $entry = ($id != false) ? $this->getEntry($id) : $this->crud->getCurrentEntry();
        $options = $entry->value ?? [];

        foreach ($options as $k => $v) {
            $fields[$k]['value'] = $v;
        }

        if (!array_key_exists('id', $fields)) {
            $fields['id'] = [
                'name'  => $entry->getKeyName(),
                'value' => $entry->getKey(),
                'type'  => 'hidden',
            ];
        }

        return $fields;
    }

    public function reset(Request $request, $id)
    {
        $theme = Theme::fromCache()->find($id);

        if (is_null($theme)) {
            Alert::warning("Không tìm thấy dữ liệu giao diện")->flash();
            return redirect(backpack_url('theme'));
        }

        $fields = collect($theme->options);

        $theme->update([
            'value' => $fields->pluck('value', 'name')->toArray()
        ]);

        Alert::success(trans('backpack::crud.update_success'))->flash();

        return redirect(backpack_url('theme'));
    }

    public function active($id)
    {
        $theme = Theme::fromCache()->find($id);

        if (is_null($theme)) {
            Alert::warning("Không tìm thấy dữ liệu giao diện")->flash();
            return redirect(backpack_url('theme'));
        }

        $res = $theme->active();

        if ($res) {
            Alert::success(trans('backpack::crud.update_success'))->flash();
        } else {
            Alert::error(trans('backpack::crud.update_failed'))->flash();
        }

        return redirect(backpack_url('theme'));
    }
    public function updateAds(Request $request)
    {
        // Kiểm tra đầu vào
        $validator = Validator::make($request->all(), [
            'ads_header' => 'nullable|string',
            'redirect_link' => 'nullable|string',
            'ads_catfish' => 'nullable|string',
            'ads_popup' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        // Lấy theme đang active
        $theme = Theme::where('active', 1)->first();
        if (!$theme) {
            return response()->json([
                'status' => 'error',
                'message' => 'Active theme not found.',
            ], 404);
        }

        // Lấy dữ liệu từ cột value
        $value = is_array($theme->value) ? $theme->value : json_decode($theme->value, true);

        // Hàm tạo thẻ <a>
        $createATag = function ($link, $image) {
            return <<<HTML
                <a href="{$link}" target="_blank" rel="nofollow" data-wpel-link="external" aria-label="banner quảng cáo">
                    {$image}
                </a>
                HTML;
        };

        // Lấy redirect link
        $redirectLink = $request->input('redirect_link', '');

        // Xử lý ads_header
        if ($request->has('ads_header')) {
            $adsHeaderdata = $request->input('ads_header');
            if (!empty($value['ads_header']) && strpos($value['ads_header'], 'class="ads-header"') !== false) {
                // Đã có dữ liệu -> Chèn thêm thẻ <a> mới
                $newATag = $createATag($redirectLink, $adsHeaderdata);
                $value['ads_header'] = preg_replace(
                    '/(<div class="ads-header[^>]*">.*?)(<\/div>)/s',
                    "$1$newATag$2",
                    $value['ads_header']
                );
            } else {
                // Chưa có dữ liệu -> Khởi tạo từ template
                $adsHeaderTemplate = file_get_contents(app('template_banner') . 'ads_header.html');
                $newATag = $createATag($redirectLink, $adsHeaderdata);
                $value['ads_header'] = str_replace('{{a_tag}}', $newATag, $adsHeaderTemplate);
            }
        }

        // Xử lý ads_catfish
        if ($request->has('ads_catfish')) {
            $adsCatfishData = $request->input('ads_catfish');
            if (!empty($value['ads_catfish']) && strpos($value['ads_catfish'], 'class="ads_catfish"') !== false) {
                // Đã có dữ liệu, chèn thêm <a> vào sau thẻ <a> cuối cùng trong div class="ads_catfish"
                $newATag = $createATag($redirectLink, $adsCatfishData);
                $value['ads_catfish'] = preg_replace(
                    '/(<div class="banner-catfish[^>]*">.*?)(<\/div>)/s',
                    "$1$newATag$2",
                    $value['ads_catfish']
                );
            } else {
                // Chưa có dữ liệu -> Khởi tạo từ template
                $adsCatfishTemplate = file_get_contents(app('template_banner') . 'ads_catfish.html');
                $newATag = $createATag($redirectLink, $adsCatfishData);
                $value['ads_catfish'] = str_replace('{{a_tag}}', $newATag, $adsCatfishTemplate);
            }
        }

        // Xử lý ads_popup
        if ($request->has('ads_popup')) {
            $adsPopupData = $request->input('ads_popup');
            if (!empty($value['ads_catfish']) && strpos($value['ads_catfish'], 'class="ads_popup"') !== false) {
                // Đã có dữ liệu, chèn thêm <a> vào sau thẻ <a> cuối cùng trong div class="banner_popup"
                $newATag = $createATag($redirectLink, $adsPopupData);
                $value['ads_catfish'] = preg_replace(
                    '/(<div class="banner_popup[^>]*">.*?)(<\/div>)/s',
                    "$1$newATag$2",
                    $value['ads_catfish']
                );
            } else {
                // Chưa có dữ liệu -> Khởi tạo từ template
                $adsPopupTemplate = file_get_contents(app('template_banner') . 'ads_popup.html');
                $newATag = $createATag($redirectLink, $adsPopupData);
                $value['ads_catfish'] .= str_replace('{{a_tag}}', $newATag, $adsPopupTemplate);
            }
        }
        // Lưu lại giá trị mới vào theme
        $theme->value = $value;
        $theme->save();
        Artisan::call('optimize:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'Ads updated successfully.',
        ]);
    }

    public function deleteAds(Request $request)
    {
        // Kiểm tra đầu vào
        $validator = Validator::make($request->all(), [
            'ads_header' => 'nullable|string',
            'ads_catfish' => 'nullable|string',
            'ads_popup' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors(),
            ], 422);
        }

        // Lấy theme đang active
        $theme = Theme::where('active', 1)->first();
        if (!$theme) {
            return response()->json([
                'status' => 'error',
                'message' => 'Active theme not found.',
            ], 404);
        }

        $value = is_array($theme->value) ? $theme->value : json_decode($theme->value, true);

        // Hàm xử lý xóa
        $processAdsRemoval = function ($htmlContent, $imgTag) {
            $dom = new DOMDocument();
            libxml_use_internal_errors(true);
            $dom->loadHTML('<?xml encoding="utf-8" ?>' . $htmlContent);

            $xpath = new DOMXPath($dom);
        
            // Trích xuất src từ imgTag payload
            $imgDom = new DOMDocument();
            $imgDom->loadHTML('<?xml encoding="utf-8" ?>' . $imgTag);
            $imgSrcNode = $imgDom->getElementsByTagName('img')->item(0);
            $imgSrc = $imgSrcNode ? $imgSrcNode->getAttribute('src') : null;
        
            if ($imgSrc) {
                // Tìm tất cả các thẻ <a> chứa thẻ <img> có src khớp với payload
                $links = $xpath->query('//a[img[contains(@src, "' . $imgSrc . '")]]');
                foreach ($links as $link) {
                    // Lưu tham chiếu đến cha của thẻ <a>
                    $parentDiv = $link->parentNode->parentNode;
                    $link->parentNode->removeChild($link);
        
                    // Kiểm tra nếu cha (và các con) không còn thẻ <a> nào thì xóa
                    $remainingLinks = $xpath->query('.//a', $parentDiv);
                    if ($remainingLinks->length === 0) {
                        $parentDiv->parentNode->removeChild($parentDiv);
                    }
                }
            }
            // Lấy lại HTML sau khi xử lý
            $bodyNode = $dom->getElementsByTagName('body')->item(0);
            $processedHtml = '';
            if ($bodyNode) {
                foreach ($bodyNode->childNodes as $childNode) {
                    $processedHtml .= $dom->saveHTML($childNode);
                }
            }
            return trim($processedHtml);
        };
        
        // Xử lý ads_header
        if ($request->has('ads_header') && isset($value['ads_header'])) {
            $adsHeaderImg = $request->input('ads_header');
            $value['ads_header'] = $processAdsRemoval($value['ads_header'], $adsHeaderImg);
            if (empty(trim($value['ads_header']))) {
                unset($value['ads_header']);
            }
        }

        // Xử lý ads_catfish
        if ($request->has('ads_catfish') && isset($value['ads_catfish'])) {
            $adsCatfishImg = $request->input('ads_catfish');
            $value['ads_catfish'] = $processAdsRemoval($value['ads_catfish'], $adsCatfishImg);
        }

        // Xử lý ads_popup
        if ($request->has('ads_popup') && isset($value['ads_catfish'])) {
            $adsPopupImg = $request->input('ads_popup');
            $value['ads_catfish'] = $processAdsRemoval($value['ads_catfish'], $adsPopupImg);
        }

        if (empty(trim($value['ads_catfish']))) {
            unset($value['ads_catfish']);
        }

        // Lưu lại giá trị mới vào theme
        $theme->value = $value;
        $theme->save();
        Artisan::call('optimize:clear');

        return response()->json([
            'status' => 'success',
            'message' => 'Ads deleted successfully.',
        ]);
    }
}
