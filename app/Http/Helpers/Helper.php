<?php


use App\Classes\Settings;
use App\Models\Usergroup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Valuestore\Valuestore;

function _GET($url, $payload = []) : array|bool
{
    $response = Http::timeout(10000)->get($url);
    if($response->status() == 200 )
    {
        return json_decode($response->body(), true) ??  true;
    }
    return false;
}

function _FETCH($url) : array|bool
{
    $response = Http::timeout(10000)->get($url);

    if($response->status() == 200 )
    {
        return json_decode($response->body(), true) ??  true;
    }
    return false;
}

function _POST($url, $payload = []) : array|bool
{
    $response =   Http::timeout(10000)->post($url, $payload);

    if($response->status() == 200 )
    {
        return json_decode($response->body(), true) ??  true;
    }

    Storage::disk('local')->append('bulk-logs', $response->body(), null);

    return false;
}

if (!function_exists('isJson')) {
    function isJson($string)
    {
        if(is_array($string) || is_object($string)) return true;
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

function generateRandom($length = 25) {
    $characters = 'abcdefghijklmnopqrstuvwxyz_';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}






function str_plural($name){
    return Str::plural($name);
}



function getStoreSettings(){

    return json_decode(json_encode(Valuestore::make(storage_path('app/settings.json'))->all()));
}

function month_year($time = false, $pad = false)
{
    if (!$time) $time = time() + time_offset();
    else $time = strtotime($time);
    if ($pad) $pad = ". h:i:s A";
    else $pad = "";
    return date('F, Y' . $pad, $time);
}

function time_offset()
{
    return 0;
}

function eng_str_date($time = false, $pad = false)
{
    if (!$time) $time = time() + time_offset();
    else $time = strtotime($time);
    if ($pad) $pad = ". h:i:s A";
    else $pad = "";
    return date('d/m/Y' . $pad, $time);
}

function human_date($date){
    return (new Carbon($date))->format('F jS, Y');
}

function twentyfourHourClock($time)
{
    return  date('H:i', strtotime($time));
}

function twelveHourClock($time)
{
    return  date('h:i A', strtotime($time));
}

function mysql_str_date($time = false, $pad = false)
{
    if (!$time) $time = time() + time_offset();
    else $time = strtotime($time);
    if ($pad) $pad = ". h:i:s A";
    else $pad = "";
    return date('Y-m-d' . $pad, $time);
}

function str_date($time = false, $pad = false)
{
    if (!$time) $time = time() + time_offset();
    else $time = strtotime($time);
    if ($pad) $pad = ". h:i:s A";
    else $pad = "";
    return date('l, F jS, Y' . $pad, $time);
}

function str_date2($time = false, $pad = false)
{
    if (!$time) $time = time() + time_offset();
    else $time = strtotime($time);
    if ($pad) $pad = ". h:i:s A";
    else $pad = "";
    return date('D, F jS, Y' . $pad, $time);
}

function format_date($date, $withTime = TRUE)
{
    if ($date == "0000-00-00 00:00:00") {
        return "Never";
    }

    $date = trim($date);
    $retVal = "";
    $date_time_array = explode(" ", $date);
    $time = $date_time_array[1];
    $time_array = explode(":", $time);

    $date_array = explode("-", "$date");
    $day = $date_array['2'];
    $month = $date_array['1'];
    $year = $date_array['0'];
    if ($year > 0) {
        @ $ddate = mktime(12, 12, 12, $month, $day, $year);
        @ $retVal = date("j M Y", $ddate);
    }

    if (!empty($time)) {
        $hr = $time_array[0];
        $min = $time_array[1];
        $sec = $time_array[2];
        @ $ddate = mktime($hr, $min, $sec, $month, $day, $year);
        @ $retVal = date("j M Y, H:i", $ddate);
        if (!$withTime) {
            @ $retVal = date("j M Y", $ddate);
        }
    }

    return $retVal;
}

function restructureDate($date_string)
{
    if (strtotime($date_string)) return $date_string;

    if (str_contains($date_string, "/")) {
        if (strtotime(str_replace("/", "-", $date_string))) return str_replace("/", "-", $date_string);

        // TODO: try to change the date format to make it easier for the system to parse
    }

    return $date_string;
}

function render($type = "append")
{
    echo "@render:$type=out>>";
}

function json_success($data)
{
    return json_status(true, $data, 'success');
}

function json_failure($data, $code_name)
{
    return json_status(false, $data, $code_name);
}

function response_array_failure($data, $code_name)
{
    return response_array_status(false, $data, $code_name);
}

function json_status($status, $data, $code_name)
{

    $response = response_array_status($status, $data, $code_name);

    return json($response);
}

function response_array_status($status, $data, $code_name)
{
    if (!$statuses = config("statuses." . $code_name)) $statuses = config("statuses.unknown");

    $response = [
        'status' => $status,
        'status_code' => $statuses[0],
        'message' => $statuses[1],
    ];

    if ($status) {
        if (is_bool($data)) $data = ($data) ? "true" : "false";

        $response['data'] = $data;
        $response['validation'] = null;
        return $response;
    }

    $response['data'] = null;
    $response['validation'] = $data;
    return $response;
}

function json($response)
{
    return response()->json($response);
}

function normal_case($str)
{
    return ucwords(str_replace("_", " ", Str::snake(str_replace("App\\", "", $str))));
}

function alert_success($msg)
{
    return alert('success', $msg);
}

function alert_info($msg)
{
    return alert('info', $msg);
}

function alert_warning($msg)
{
    return alert('warning', $msg);
}

function error($msg) : string
{
    return '<span class="text-danger d-block">'.$msg.'</span>';
}

function alert_error($msg)
{
    return alert('danger', $msg);
}

function alert($status, $msg)
{
    return '<div class="alert alert-' . $status . '">' . $msg . '</div>';
}

function money($amt)
{
    return number_format($amt, 2);
}


/**
 * Return a capitalised string
 *
 * @return string
 * @param string $string
 */
function toCap($string)
{
    return strtoupper(strtolower($string));
}

/**
 * Return a small letter string
 *
 * @return string
 * @param string $string
 */
function toSmall($string)
{
    return strtolower($string);
}

/**
 * Return a sentence case string
 *
 * @return string
 * @param string $string
 */
function toSentence($string)
{
    return ucwords(strtolower($string));
}

function generateRandomString($randStringLength)
{
    $timestring = microtime();
    $secondsSinceEpoch = (integer)substr($timestring, strrpos($timestring, " "), 100);
    $microseconds = (double)$timestring;
    $seed = mt_rand(0, 1000000000) + 10000000 * $microseconds + $secondsSinceEpoch;
    mt_srand($seed);
    $randstring = "";
    for ($i = 0; $i < $randStringLength; $i++) {
        $randstring .= mt_rand(0, 9);
    }
    return ($randstring);
}


/**
 * Get IDs of the Work Groups this User has been granted permission to work on.
 * @return array
 */



function getRandomString_AlphaNum($length)
{
    //Init the pool of characters by category
    $pool[0] = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    $pool[1] = "23456789";
    return randomString_Generator($length, $pool);
}   //END getRandomString_AlphaNum()


function randomString_Num($length)
{
    //Init the pool of characters by category
    $pool[0] = "0123456789";
    return randomString_Generator($length, $pool);
}

function getRandomString_AlphaNumSigns($length)
{
    //Init the pool of characters by category
    $pool[0] = "ABCDEFGHJKLMNPQRSTUVWXYZ";
    $pool[1] = "abcdefghjkmnpqrstuvwxyz";
    $pool[2] = "23456789";
    $pool[3] = "-_";
    return randomString_Generator($length, $pool);
}

function randomString_Generator($length, $pools)
{
    $highest_pool_index = count($pools) - 1;
    //Now generate the string
    $finalResult = "";
    $length = abs((int)$length);
    for ($counter = 0; $counter < $length; $counter++) {
        $whichPool = rand(0, $highest_pool_index);    //Randomly select the pool to use
        $maxPos = strlen($pools[$whichPool]) - 1;    //Get the max number of characters in the pool to be used
        $finalResult .= $pools[$whichPool][mt_rand(0, $maxPos)];
    }
    return $finalResult;
}

/**
 * The only difference between this and date is that it works with the env time offet
 * @param $format
 * @param $signed_seconds
 * @return bool|string
 */
if (!function_exists("now")) {
    function now($format = 'Y-m-d H:i:s', $signed_seconds = 0)
    {
        return date($format, ((time() + (env('TIME_OFFSET_HOURS', 0) * 60)) + $signed_seconds));
    }
}

function removeSpecialCharacter($string)
{
    // return preg_replace('/[^A-Za-z0-9\-]/', '', $string);
    return preg_replace('/\'/', '', $string);
}



function softwareStampWithDate($width = "100px") {
    return "<br>
    Generated @". date('Y-m-d H:i A') ;
}

function string_to_secret(string $string = NULL)
{
    if (!$string) return NULL;

    $length = strlen($string);
    $visibleCount = (int) round($length / 4);
    $hiddenCount = $length - ($visibleCount * 2);

    return substr($string, 0, $visibleCount) . str_repeat('*', $hiddenCount) . substr($string, ($visibleCount * -1), $visibleCount);
}



function split_name($name)
{
    $name = trim($name);
    $last_name = (strpos($name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $name);
    $first_name = trim(preg_replace('#' . $last_name . '#', '', $name));
    return array("firstname" => $first_name, "lastname" => $last_name);
}

function convert_date($date){
    return date('D, F jS, Y', strtotime($date));
}

function convert_date_with_time($date){
    return date('D, F jS, Y h:i a', strtotime($date));
}

function convert_date2($date){
    return date('Y/m/d', strtotime($date));
}


function todaysDate(){
    return date('Y-m-d');
}

function yesterdayDate(){
    return date('Y-m-d',strtotime('yesterday'));
}

function weeklyDateRange(){
    $dt = strtotime (date('Y-m-d'));
    $range =  array (
        date ('N', $dt) == 1 ? date ('Y-m-d', $dt) : date ('Y-m-d', strtotime ('last monday', $dt)),
        date('N', $dt) == 7 ? date ('Y-m-d', $dt) : date ('Y-m-d', strtotime ('next sunday', $dt))
    );

    return $range;
}

function monthlyDateRange(){
    $dt = strtotime (date('Y-m-d'));
    $range =  array (
        date ('Y-m-d', strtotime ('first day of this month', $dt)),
        date ('Y-m-d', strtotime ('last day of this month', $dt))
    );
    return $range;
}

function getUserMenu()
{
    $groupMenu = loadUserMenu();

    $userMenus = '<li class="nav-item mb-1"><a wire:navigate class="nav-link '.("dashboard" === \Route::currentRouteName() ? 'active' : '').'" href="' . route("dashboard") . '"><i data-feather="box"></i> Dashboard</a></li>';

    if ($groupMenu) {
        $lastModule = '';
        $isFirstRun = true;

        foreach ($groupMenu as $menu) {
            if(in_array($menu->module_id, Settings::$reports)) continue;

            if(!canModuleDisplay($menu->module_id)) continue;

            if ($lastModule != $menu->module_id) {
                if ($lastModule != '' && !$isFirstRun) {
                    $userMenus .= '</nav></li>';
                }
                $isFirstRun = false;
                $userMenus .= '<li class="nav-item'.(str_contains(request()->route()->getPrefix(), strtolower($menu->module->name)) ? ' show' : '').'"><a '.(str_contains(request()->route()->getPrefix(), strtolower($menu->module->name)) ? 'class="nav-link with-sub active"' : 'class="nav-link with-sub"').' href="">
                <i data-feather="'.$menu->module->icon.'"></i>
                 '.$menu->module->label.'
                </a><nav class="nav nav-sub">';
            }
            if ($menu->visibility) $userMenus .= '<a wire:navigate class="'.($menu->route === \Route::currentRouteName() ? 'nav-sub-link active' : 'nav-sub-link').'" href="' . route($menu->route) . '">' . $menu->name . '</a>';
            $lastModule = $menu->module_id;
        }

        if (!$isFirstRun) {
            $userMenus .= '</nav></li>';
        }

    }

    return $userMenus;
}

function loadUserMenu($group_id = NULL)
{
    $group_id = $group_id === NULL ? auth()->user()->usergroup_id : $group_id;

    return Cache::remember('route-permission-'.$group_id,86400, function() use ($group_id){
        return \App\Models\Usergroup::with(['tasks'=>function ($q) {
            $q->join('modules', 'modules.id', '=', 'tasks.module_id');
            $q->orderBy('tasks.module_id', "ASC")->orderBy('tasks.id');
        },'permissions','users','tasks','group_tasks','tasks.module'])->find($group_id)->tasks;
    });
}

function accessGroups()
{
    return Cache::remember('usergroups',86400, function(){
        return Usergroup::where('status', 1)->get();
    });
}

function accessGroup($group_id)
{
    $group_id = $group_id === NULL ? auth()->user()->usergroup_id : $group_id;

    return accessGroups()->filter(function($item) use($group_id){
        return $item->id === $group_id;
    })->first();
}


function userCanView($route)
{
    if($route === "") return true;
    $route = trim($route);
    return userPermissions()->contains(function ($task, $key) use ($route) {
        return $task->route == $route;
    });
}

function canModuleDisplay($module_id)
{
    return userPermissions()->contains(function ($task, $key) use ($module_id) {
        return $module_id == $task->module_id && $task->visibility == "1";
    });

}

function userPermissions()
{
    return loadUserMenu();
}

function usergroups($active = false)
{
    $usergroups =  Cache::remember('usergroups', 86400, function(){
        return DB::table('usergroups')->get();
    });

    if($active === true) return $usergroups->filter(function($item){
        return $item->status == true;
    });

    return $usergroups;
}


if(!function_exists('auth_layout')){
    function auth_layout($layout = 'livewire.auth.login', $parameters = [])
    {
        return view($layout)->layout('layouts.app', ['login_layout' => true]);
    }
}


if(!function_exists('app_layout')){
    function app_layout($layout, $parameters = [])
    {
        return view($layout)->layout('layouts.app',);
    }
}
