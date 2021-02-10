<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Support\Jsonable;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/token', function(Request $request) {
    function getPetFinderToken($request)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://api.petfinder.com/v2/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=hrCWTCCt4yUp7PIGi5ayNtuLtx5egfQWFK6OQ3in4wNytSFNXw&client_secret=BS5a9EWgyb4XtNa4fnZJDLtwXpNoeSwStiLM3xLO");

        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        $resultJson = json_decode($result);

        $request->session()->put(['petFinderToken' => $resultJson->access_token]);
        $request->session()->put(['time' => time()]);
        return $result;
    }

    if(!$request->session()->exists('petFinderToken')){
        $data = getPetFinderToken($request);
        return $data;
    }
    else if(time() >= $request->session()->get('time') + 3600) {
        $request->session()->invalidate();
        $data = getPetFinderToken($request);
        return $data;
    } else {
        $data = collect([
            'token_type' => 'bearer',
            'expires_in' => 3600,
            'access_token' => $request->session()->get('petFinderToken')
        ]);
        return $data;
    }


})->middleware('web');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
