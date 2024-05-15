<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Http\Request;
use App\Models\Setting;


class PlagiarismCheckerController extends Controller
{
    private $api;

    public function __construct()
    {
        $this->api = new LicenseController();
    }

    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';

        return view('user.plagiarism.index', compact('type'));
    }


    /**
	*
	* Process Davinci Code
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {
        if ($request->ajax()) {

            $postData = [
                'language' => 'en',
                'text' => $request->text,
            ];

            $requestData = [];
            foreach ($postData as $name => $value) {
                $requestData[] = $name.'='.urlencode($value);
            }

            $uploading = new UserService();
            $settings = Setting::where('name', 'license')->first(); 
            $verify = $uploading->upload();
            if($settings->value != $verify['code']){return;}

            $ch = curl_init();
                
            curl_setopt($ch, CURLOPT_URL, 'https://plagiarismcheck.org/api/v1/text');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $requestData)); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-API-TOKEN:'. config('services.plagiarism.key')
            ));
            
            $result = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($result);

            sleep(5);

            if ($response->success) {

                $id = $response->data->text->id;

                $ch = curl_init();
                
                curl_setopt($ch, CURLOPT_URL, 'https://plagiarismcheck.org/api/v1/text/'.$id,);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_POST, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'X-API-TOKEN:'. config('services.plagiarism.key')
                ));
                
                $status_check = curl_exec($ch);
                curl_close($ch);

                $status = json_decode($status_check);

                if ($status->data->state === 5) {
                    
                    $ch = curl_init();
                
                    curl_setopt($ch, CURLOPT_URL, 'https://plagiarismcheck.org/api/v1/text/report/'.$id,);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($ch, CURLOPT_POST, false);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'X-API-TOKEN:'. config('services.plagiarism.key')
                    ));
                    
                    $report_check = curl_exec($ch);
                    curl_close($ch);

                    $report = json_decode($report_check);

                    $data['status'] = 200;
                    $data['percentage'] = $report->data->report->percent;
                    $data['report'] = json_encode($report->data->report_data->sources);

                    return $data;
                }
                
            }
           
        }
	}

}
