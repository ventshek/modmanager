<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace Pterodactyl\Http\Controllers\Admin;

use function GuzzleHttp\Psr7\str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;

class ModsController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * ModsController constructor.
     * @param AlertsMessageBag $alert
     */
    public function __construct(AlertsMessageBag $alert)
    {
        $this->alert = $alert;
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $mods = DB::table('mods')->get();

        return view('admin.mods.index', [
            'mods' => $mods
        ]);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function new()
    {
        $eggs = DB::table('eggs')->get();

        return view('admin.mods.new', ['eggs' => $eggs]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|min:1|max:80',
            'version' => 'required',
            'category' => 'required|min:1|max:80',
            'description' => 'required|min:1|max:200',
            'egg_ids' => 'required',
            'download' => 'required|min:1|max:190',
            'install' => 'required|min:1|max:190',
            'remove' => 'required|min:1|max:190'
        ]);

        $name = trim(strip_tags($request->input('name')));
        $version = trim(strip_tags($request->input('version')));
        $category = ucfirst(trim(strip_tags($request->input('category'))));
        $description = trim($request->input('description'));
        $egg_ids = $request->input('egg_ids');
        $download = trim($request->input('download'));
        $install = trim($request->input('install'));
        $remove = trim($request->input('remove'));

        DB::table('mods')->insert([
            'name' => $name,
            'description' => $description,
            'category_name' => $category,
            'version' => $version,
            'egg_id' => implode(',', $egg_ids),
            'download_url_zip' => $download,
            'install_folder' => $install,
            'uninstall_name' => $remove
        ]);

        $this->alert->success('You have successfully created new mod.')->flash();
        return redirect()->route('admin.mods');
    }

    /**
     * @param $mod_id
     * @return \Illuminate\Contracts\View\Factory|RedirectResponse|\Illuminate\View\View
     */
    public function edit($mod_id)
    {
        $mod_id = (int) $mod_id;

        $mod = DB::table('mods')->where('id', '=', $mod_id)->get();
        if (count($mod) < 1) {
            $this->alert->danger('Mod not found!')->flash();
            return redirect()->route('admin.mods');
        }

        $eggs = DB::table('eggs')->get();

        return view('admin.mods.edit', ['mod' => $mod[0], 'eggs' => $eggs]);
    }

    /**
     * @param Request $request
     * @param $mod_id
     * @return \Illuminate\Http\JsonResponse|RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, $mod_id)
    {
        $mod_id = (int) $mod_id;

        $domain = DB::table('mods')->where('id', '=', $mod_id)->get();
        if (count($domain) < 1) {
            $this->alert->danger('Mod not found.')->flash();
            return redirect()->route('admin.mods');
        }

        $this->validate($request, [
            'name' => 'required|min:1|max:80',
            'version' => 'required',
            'category' => 'required|min:1|max:80',
            'description' => 'required|min:1|max:200',
            'egg_ids' => 'required',
            'download' => 'required|min:1|max:190',
            'install' => 'required|min:1|max:190',
            'remove' => 'required|min:1|max:190'
        ]);

        $name = trim(strip_tags($request->input('name')));
        $version = trim(strip_tags($request->input('version')));
        $category = ucfirst(trim(strip_tags($request->input('category'))));
        $description = trim($request->input('description'));
        $egg_ids = $request->input('egg_ids');
        $download = trim($request->input('download'));
        $install = trim($request->input('install'));
        $remove = trim($request->input('remove'));

        DB::table('mods')->where('id', '=', $mod_id)->update([
            'name' => $name,
            'description' => $description,
            'category_name' => $category,
            'version' => $version,
            'egg_id' => implode(',', $egg_ids),
            'download_url_zip' => $download,
            'install_folder' => $install,
            'uninstall_name' => $remove
        ]);

        $this->alert->success('You have successfully edited this mod.')->flash();
        return redirect()->route('admin.mods.edit', $mod_id);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $mod_id = (int) $request->input('id', '');

        $mod = DB::table('mods')->where('id', '=', $mod_id)->get();
        if (count($mod) < 1) {
            return response()->json(['error' => 'Mod not found.'])->setStatusCode(500);
        }

        DB::table('mods')->where('id', '=', $mod_id)->delete();

        return response()->json(['success' => true]);
    }
}
