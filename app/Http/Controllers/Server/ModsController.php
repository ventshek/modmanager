<?php

namespace Pterodactyl\Http\Controllers\Server;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Repositories\Daemon\ModsRepository;
use Pterodactyl\Traits\Controllers\JavascriptInjection;

class ModsController extends Controller
{
    use JavascriptInjection;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * @var \Pterodactyl\Repositories\Daemon\ModsRepository
     */
    protected $modsRepository;

    /**
     * ModsController constructor.
     * @param AlertsMessageBag $alert
     * @param ModsRepository $modsRepository
     */
    public function __construct(AlertsMessageBag $alert, ModsRepository $modsRepository)
    {
        $this->alert = $alert;
        $this->modsRepository = $modsRepository;
    }

    /**
     * @param Request $request
     * @return View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request): View
    {
        $server = $request->attributes->get('server');
        $this->authorize('view-mods', $server);
        $this->setRequest($request)->injectJavascript();

        $mods = DB::table('mods')->where('egg_id', '=', $server->egg_id)->get();
        $categories = [];
        $modsToView = [];

        $installedMods = DB::table('installed_mods')->where('server_id', '=', $server->id)->get();

        foreach ($mods as $mod) {
            $category = trim(strtolower($mod->category_name));

            if (!isset($categories[$category])) {
                $categories[$category] = $mod->category_name;
            }

            $mod->installed = false;

            foreach ($installedMods as $installedMod) {
                if ($mod->id == $installedMod->mod_id && $server->id == $installedMod->server_id) {
                    $mod->installed = true;
                }
            }

            $modsToView[$category][] = $mod;
        }

        return view('server.mods', [
            'categories' => $categories,
            'mods' => $modsToView,
            'installedMods' => $installedMods
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function install(Request $request): JsonResponse
    {
        $server = $request->attributes->get('server');
        $this->setRequest($request)->injectJavascript();

        $mod_id = (int) $request->input('mod_id');

        $mod = DB::table('mods')->where('id', '=', $mod_id)->where('egg_id', '=', $server->egg_id)->get();
        if (count($mod) < 1) {
            return response()->json(['success' => false, 'error' => 'Mod not found!']);
        }

        $isInstalled = DB::table('installed_mods')->where('server_id', '=', $server->id)->where('mod_id', '=', $mod_id)->get();
        if (count($isInstalled) > 0) {
            return response()->json(['success' => false, 'error' => 'This mod is installed!']);
        }

        $install = $this->modsRepository->setServer($server)->install([
            'url' => $mod[0]->download_url_zip,
            'folder' => $mod[0]->install_folder
        ]);

        if (json_decode($install->getBody())->success != "true") {
            return response()->json(['success' => false, 'error' => 'Failed to install mod!' . $install->getBody()]);
        }

        DB::table('installed_mods')->insert([
            'server_id' => $server->id, 'mod_id' => $mod_id
        ]);

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function uninstall(Request $request): JsonResponse
    {
        $server = $request->attributes->get('server');
        $this->setRequest($request)->injectJavascript();

        $mod_id = (int) $request->input('mod_id');

        $mod = DB::table('mods')->where('id', '=', $mod_id)->where('egg_id', '=', $server->egg_id)->get();
        if (count($mod) < 1) {
            return response()->json(['success' => false, 'error' => 'Mod not found!']);
        }

        $isInstalled = DB::table('installed_mods')->where('server_id', '=', $server->id)->where('mod_id', '=', $mod_id)->get();
        if (count($isInstalled) < 1) {
            return response()->json(['success' => false, 'error' => 'This mod isn\'t installed!']);
        }

        $uninstall = $this->modsRepository->setServer($server)->uninstall([
            'folder' => '/' . $mod[0]->install_folder . '/' . $mod[0]->uninstall_name
        ]);

        if (json_decode($uninstall->getBody())->success != "true") {
            return response()->json(['success' => false, 'error' => 'Failed to uninstall mod!']);
        }

        DB::table('installed_mods')->where('mod_id', '=', $mod_id)->where('server_id', '=', $server->id)->delete();

        return response()->json([
            'success' => true
        ]);
    }
}
