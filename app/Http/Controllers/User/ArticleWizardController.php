<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Traits\VoiceToneTrait;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\ArticleWizard;
use App\Models\FineTuneModel;
use App\Services\HelperService;
use Exception;


class ArticleWizardController extends Controller
{
    use VoiceToneTrait;

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
        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $fine_tunes = FineTuneModel::all();

        # Check user permission to use the feature
        if (auth()->user()->group == 'user') {
            if (config('settings.wizard_access_user') != 'allow') {
               toastr()->warning(__('AI Article Wizard feature is not available for free tier users, subscribe to get a proper access'));
               return redirect()->route('user.plans');
            } else {
                $languages = Language::orderBy('languages.language', 'asc')->get();

                $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

                $wizard = ArticleWizard::where('user_id', auth()->user()->id)->where('current_step', '!=', 5)->first();

                if (!$wizard) {
                    $wizard = new ArticleWizard();
                    $wizard->user_id = auth()->user()->id;
                    $wizard->save();
                }

                $wizard = ArticleWizard::find($wizard->id)->toArray();

                return view('user.templates.wizard.index', compact('languages', 'workbooks', 'wizard', 'models', 'fine_tunes'));
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->wizard_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI Article Wizard feature'));
                return redirect()->back();                   
            } else {
                $languages = Language::orderBy('languages.language', 'asc')->get();

                $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

                $wizard = ArticleWizard::where('user_id', auth()->user()->id)->where('current_step', '!=', 5)->first();

                if (!$wizard) {
                    $wizard = new ArticleWizard();
                    $wizard->user_id = auth()->user()->id;
                    $wizard->save();
                }

                $wizard = ArticleWizard::find($wizard->id)->toArray();

                return view('user.templates.wizard.index', compact('languages', 'workbooks', 'wizard', 'models', 'fine_tunes'));
            }
        } else {
            $languages = Language::orderBy('languages.language', 'asc')->get();

            $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

            $wizard = ArticleWizard::where('user_id', auth()->user()->id)->where('current_step', '!=', 5)->first();

            if (!$wizard) {
                $wizard = new ArticleWizard();
                $wizard->user_id = auth()->user()->id;
                $wizard->save();
            }

            $wizard = ArticleWizard::find($wizard->id)->toArray();

            return view('user.templates.wizard.index', compact('languages', 'workbooks', 'wizard', 'models', 'fine_tunes'));
        }
        
    }


    /**
	*
	* Generate keywords
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function keywords(Request $request) 
    {
        if ($request->ajax()) {
            $max_tokens = 50;

           
            # Check Openai APIs
            $key = $this->getOpenai();
            if ($key == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                return $data; 
            }

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, $max_tokens);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }
  
            try {
                $response = OpenAI::chat()->create([
                    'model' => $request->model,
                    'messages' => [[
                        'role' => 'user',
                        'content' => "You're an SEO Expert. Generate $request->keywords_numbers keywords in $request->language about '$request->topic'. Must result as a comma separated string without any extra details. Result format is: keyword1, keyword2, ..., keywordN. Must not write ```json."
                    ]]
                ]);

                # Update credit balance
                $words = count(explode(' ', ($response['choices'][0]['message']['content'])));
                HelperService::updateBalance($words, $request->model); 

                $flag = Language::where('language_code', $request->language)->first();

                $wizard = ArticleWizard::where('id', $request->wizard)->first();
                if (is_null($wizard->keywords)) {
                    $wizard->keywords = $response['choices'][0]['message']['content'];
                } else {
                    $wizard->keywords .= ', ' . $response['choices'][0]['message']['content'];
                }          
                $wizard->language = $flag->language;          
                $wizard->tone = $request->tone;          
                $wizard->creativity = (float)$request->creativity;          
                $wizard->view_point = $request->view_point;          
                $wizard->max_words = $request->words;
                $wizard->additional_information = $request->additional_information;
                \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);         
                $wizard->save();

                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $words;
                $data['type'] = (auth()->user()->available_words == -1) ? 'unlimited' : 'counted';

                return response()->json(['result' => $response['choices'][0]['message']['content'], 'balance' => $data]);

            } catch (Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an issue with keywords generation, please try again') . $e->getMessage();
                return $data; 
            }
        }
	}


    /**
	*
	* Generate ideas
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function ideas(Request $request) 
    {
        if ($request->ajax()) {
            $max_tokens = 50;

           
            # Check Openai APIs
            $key = $this->getOpenai();
            if ($key == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                return $data; 
            }

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, $max_tokens);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            try {

                if (!is_null($request->keywords) || $request->keywords != '') {
                    $prompt = "Generate $request->topics_number engaging titles. The titles have to motivate the user to click. Titles must be about topic:  $request->topic . Write in the following language: $request->language and include following keywords in the titles: $request->keywords. (Without number for order). Must not write any description. Strictly create in array json data. Every title is sentence or phrase string. The depth is 1. This is result format: [title1, title2, ..., titlen].  Must not write ```json.";
                } else {
                    $prompt = "Generate $request->topics_number engaging titles.The titles have to motivate the user to click. Titles must be about topic:  $request->topic . (Without number for order, titles are not keywords). Write in the following language: $request->language. Must not write any description. Strictly create in array json data. Every title is sentence or phrase string. The depth is 1. This is result format: [title1, title2, ..., titlen]. Must not write ```json.";
                    //echo 'data: ' . $prompt ."\n\n";
                }
                //echo the prompt

                $response = OpenAI::chat()->create([
                    'model' => $request->model,
                    'messages' => [[
                        'role' => 'user',
                        'content' => $prompt,
                    ]]
                ]);
                
                $result = json_decode($response['choices'][0]['message']['content']);
       
                $main_string = '';
                $numItems = count($result);
                $i = 0;
                foreach ($result as $key => $value) {
                    if (++$i == $numItems) {
                        $main_string .= $value;
                    } else {
                        $main_string .= $value . ', ';
                    }
                }

                # Update credit balance
                $words = count(explode(' ', ($response['choices'][0]['message']['content'])));
                HelperService::updateBalance($words, $request->model); 

                $wizard = ArticleWizard::where('id', $request->wizard)->first();
                if (is_null($wizard->titles)) {
                    $wizard->titles = $main_string;
                } else {
                    $wizard->titles .= ', ' . $main_string;
                }
                $flag = Language::where('language_code', $request->language)->first();
                $wizard->language = $flag->language;          
                $wizard->tone = $request->tone;          
                $wizard->creativity = (float)$request->creativity;          
                $wizard->view_point = $request->view_point;  
                $wizard->max_words = $request->words;
                $wizard->additional_information = $request->additional_information;
                \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);  
                $wizard->current_step = 1;
                $wizard->save();

                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $words;
                $data['type'] = (auth()->user()->available_words == -1) ? 'unlimited' : 'counted';

                return response()->json(['result' => $main_string, 'balance' => $data]);

            } catch (Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an issue with ideas generation, please try again') . $e->getMessage();
                return $data; 
            }
        }
	}


    /**
	*
	* Generate outlines
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function outlines(Request $request) 
    {
        if ($request->ajax()) {
            $max_tokens = 50;

           
            # Check Openai APIs
            $key = $this->getOpenai();
            if ($key == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                return $data; 
            }

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, $max_tokens);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            try {

                if (!is_null($request->keywords) || $request->keywords != '') {
                    $prompt = "The keywords of article are $request->keywords. Generate different outlines related to $request->title (Each outline must has only $request->outline_subtitles subtitles (Without number for order, subtitles are not keywords)) $request->outline_number times. Provide response in the exat same language as the title. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. The depth is 1.  Must not write any description. Result must be array json data. Every subtitle is sentence or phrase string. This is result format: [[subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->outline_subtitles(string)]]. Must not write ```json.";
                } else {
                    $prompt = "Generate different outlines related to $request->title (Each outline must has only $request->outline_subtitles subtitles (Without number for order, subtitles are not keywords)) $request->outline_number times. Provide response in the exat same language as the title. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. The depth is 1.  Must not write any description. Result must be array json data. Every subtitle is sentence or phrase string. This is result format: [[subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->outline_subtitles(string)]]. Must not write ```json.";
                }

                $response = OpenAI::chat()->create([
                    'model' => $request->model,
                    'messages' => [[
                        'role' => 'user',
                        'content' => $prompt,
                    ]],
                    'temperature' => (float)$request->creativity,
                ]);

                $temp = str_replace('```json', '', $response['choices'][0]['message']['content']);
                $temp = str_replace('```', '', $temp);
                
                # Update credit balance
                $words = count(explode(' ', ($response['choices'][0]['message']['content'])));
                HelperService::updateBalance($words, $request->model); 

                $flag = Language::where('language_code', $request->language)->first();

                $wizard = ArticleWizard::where('id', $request->wizard)->first();
                $wizard->selected_title = $request->title;
                $wizard->selected_keywords = $request->keywords;
                $wizard->language = $flag->language;          
                $wizard->tone = $request->tone;          
                $wizard->creativity = (float)$request->creativity;          
                $wizard->view_point = $request->view_point;  
                $wizard->max_words = $request->words;
                $wizard->additional_information = $request->additional_information;
                \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);  
                $wizard->current_step = 2;
                $wizard->save();

                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $words;
                $data['type'] = (auth()->user()->available_words == -1) ? 'unlimited' : 'counted';

                return response()->json(['result' => json_decode($response['choices'][0]['message']['content']), 'balance' => $data]);

            } catch (Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an issue with ideas generation, please try again') . $e->getMessage();
                return $data; 
            }
        }
	}


    /**
	*
	* Generate talking points
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function talkingPoints(Request $request) 
    {
        if ($request->ajax()) {
            $max_tokens = 50;

           
            # Check Openai APIs
            $key = $this->getOpenai();
            if ($key == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                return $data; 
            }

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, $max_tokens);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            try {

                $outlines = json_decode($request->target_outlines);
                $results = [];
                $input = [];
                $total_words = 0;

                foreach ($outlines as $key=>$outline) {
                    if ($outline == '') {
                        continue;
                    } else {
                        if (!is_null($request->keywords)) {
                            $prompt = "Generate $request->points_number talking points for this outline: $outline. It must be also relevant to this title: $request->title. Provide talking points in the exact same language as the outline. Use following keywords in the talking points: $request->keywords. The depth is 1.  Must not write any description. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. Strictly create in json array of objects. This is result format: [talking_point1(string), talking_point2(string), talking_point3(string), ...]. Maximum length of each talking point must be $request->points_length words. Must not write ```json.";
                        } else {
                            $prompt = "Generate $request->points_number talking points for this outline: $outline. It must be also relevant to this title: $request->title. Provide talking points in the exact same language as the outline. The depth is 1.  Must not write any description. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. Strictly create in json array of objects. This is result format: [talking_point1(string), talking_point2(string), talking_point3(string), ...]. Maximum length of each talking point must be $request->points_length words. Must not write ```json.";
                        }
    
                        $response = OpenAI::chat()->create([
                            'model' => $request->model,
                            'messages' => [[
                                'role' => 'user',
                                'content' => $prompt,
                            ]],
                            'temperature' => (float)$request->creativity,
                        ]);

                        $temp = str_replace('```json', '', $response['choices'][0]['message']['content']);
                        $temp = str_replace('```', '', $temp);

                        # Update credit balance
                        $words = count(explode(' ', ($response['choices'][0]['message']['content'])));
                        $total_words += $words;

                        $results[$key] = json_decode($temp);
                        $input[$key] = $outline;
                    }                    
                }

                HelperService::updateBalance($total_words, $request->model);

                $flag = Language::where('language_code', $request->language)->first();

                $wizard = ArticleWizard::where('id', $request->wizard)->first();
                $wizard->selected_title = $request->title;
                $wizard->outlines = $request->target_outlines;
                $wizard->selected_keywords = $request->keywords;
                $wizard->language = $flag->language;          
                $wizard->tone = $request->tone;          
                $wizard->creativity = (float)$request->creativity;          
                $wizard->view_point = $request->view_point;  
                $wizard->max_words = $request->words;
                $wizard->additional_information = $request->additional_information;
                \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);  
                $wizard->current_step = 3;
                $wizard->save();

                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $total_words;
                $data['type'] = (auth()->user()->available_words == -1) ? 'unlimited' : 'counted';
                
                return response()->json(['result' => json_encode($results), 'input' => json_encode($input), 'balance' => $data]);

            } catch (Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an issue with talking points generation, please try again') . $e->getMessage();
                return $data; 
            }
        }
	}


    /**
	*
	* Generate images
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function images(Request $request) 
    {
        if ($request->ajax()) {

            if ($request->image_size == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('Image generation is disabled for AI Article Wizard, please proceed with the next step');
                return $data;           
            }
           
            # Check Openai APIs
            $key = $this->getOpenai();
            if ($key == 'none') {
                $data['status'] = 'error';
                $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                return $data; 
            }
            
            $vendor = '';
            # Verify if user has enough credits
            if (config('settings.wizard_image_vendor') == 'dall-e-2' || config('settings.wizard_image_vendor') == 'dall-e-3' || config('settings.wizard_image_vendor') == 'dall-e-3-hd') {
                $vendor = 'dalle';
                if (auth()->user()->available_dalle_images != -1) {
                    if ((auth()->user()->available_dalle_images + auth()->user()->available_dalle_images_prepaid) < 1) {
                        if (!is_null(auth()->user()->member_of)) {
                            if (auth()->user()->member_use_credits_image) {
                                $member = User::where('id', auth()->user()->member_of)->first();
                                if (($member->available_dalle_images + $member->available_dalle_images_prepaid) < 1) {
                                    $data['status'] = 'error';
                                    $data['message'] = __('Not enough Dalle image balance to proceed, subscribe or top up your image balance and try again');
                                    return $data;
                                }
                            } else {
                                $data['status'] = 'error';
                                $data['message'] = __('Not enough Dalle image balance to proceed, subscribe or top up your image balance and try again');
                                return $data;
                            }
                            
                        } else {
                            $data['status'] = 'error';
                            $data['message'] = __('Not enough Dalle image balance to proceed, subscribe or top up your image balance and try again');
                            return $data;
                        } 
                    }
                }
            } else {
                $vendor = 'sd';
                if (auth()->user()->available_sd_images != -1) {
                    if ((auth()->user()->available_sd_images + auth()->user()->available_sd_images_prepaid) < 1) {
                        if (!is_null(auth()->user()->member_of)) {
                            if (auth()->user()->member_use_credits_image) {
                                $member = User::where('id', auth()->user()->member_of)->first();
                                if (($member->available_sd_images + $member->available_sd_images_prepaid) < 1) {
                                    $data['status'] = 'error';
                                    $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                                    return $data;
                                }
                            } else {
                                $data['status'] = 'error';
                                $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                                return $data;
                            }
                            
                        } else {
                            $data['status'] = 'error';
                            $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                            return $data;
                        } 
                    }
                }
            }
            

            $response = '';
            $storage = '';
            $image_url = '';
            $identify = $this->api->verify_license();
            if($identify['data']!=633855){return false;}

            

            if (!is_null($request->image_description) || $request->image_description != '') {
                $prompt = $request->image_description;
            } else {
                $prompt = $request->title;
            }


            try {
                if (config('settings.wizard_image_vendor') == 'dall-e-2' || config('settings.wizard_image_vendor') == 'dall-e-3') {
                    $response = OpenAI::images()->create([
                        'model' => config('settings.wizard_image_vendor'),
                        'prompt' => $prompt,
                        'size' => $request->image_size,
                        'n' => 1,
                        "response_format" => "url",
                    ]);

                } elseif(config('settings.wizard_image_vendor') == 'dall-e-3-hd') {
                    $response = OpenAI::images()->create([
                        'model' => 'dall-e-3',
                        'prompt' => $prompt,
                        'size' => $request->image_size,
                        'n' => 1,
                        "response_format" => "url",
                        'quality' => "hd",
                    ]);

                } elseif(config('settings.wizard_image_vendor') == 'stable-diffusion-v1-6' || config('settings.wizard_image_vendor') == 'stable-diffusion-xl-1024-v1-0') {
                    $url = 'https://api.stability.ai/v1/generation/' . config('settings.wizard_image_vendor') . '/text-to-image';

                    $headers = [
                        'Authorization:' . config('services.stable_diffusion.key'),
                        'Content-Type: application/json',
                    ];

                    $resolutions = explode('x', $request->image_size);
                    $width = $resolutions[0];
                    $height = $resolutions[1];
                    $data['text_prompts'][0]['text'] = $prompt;
                    $data['text_prompts'][0]['weight'] = 1;
                    $data['height'] = (int)$height; 
                    $data['width'] = (int)$width;
                    $postdata = json_encode($data);

                    $ch = curl_init($url); 
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $result = curl_exec($ch);
                    curl_close($ch);

                    $response = json_decode($result , true);

                    if (isset($response['artifacts'])) {
                        foreach ($response['artifacts'] as $key => $value) {
    
                            $image = base64_decode($value['base64']);
    
                            $name = 'sd-' . Str::random(10) . '.png';
    
                            if (config('settings.default_storage') == 'local') {
                                Storage::disk('public')->put('images/' . $name, $image);
                                $image_url = 'images/' . $name;
                                $storage = 'local';
                            } elseif (config('settings.default_storage') == 'aws') {
                                Storage::disk('s3')->put('images/' . $name, $image, 'public');
                                $image_url = Storage::disk('s3')->url('images/' . $name);
                                $storage = 'aws';
                            } elseif (config('settings.default_storage') == 'r2') {
                                Storage::disk('r2')->put('images/' . $name, $image, 'public');
                                $image_url = Storage::disk('r2')->url('images/' . $name);
                                $storage = 'r2';
                            } elseif (config('settings.default_storage') == 'wasabi') {
                                Storage::disk('wasabi')->put('images/' . $name, $image);
                                $image_url = Storage::disk('wasabi')->url('images/' . $name);
                                $storage = 'wasabi';
                            }    
                        }
    
                    } else {
    
                        if (isset($response['name'])) {
                            if ($response['name'] == 'insufficient_balance') {
                                $message = __('You do not have sufficent balance in your Stable Diffusion account to generate new images');
                            } else {
                                $message =  __('There was an issue generating your AI Image, please try again or contact support team');
                            }
                        } else {
                           $message = __('There was an issue generating your AI Image, please try again or contact support team');
                        }
    
                        $data['status'] = 'error';
                        $data['message'] = $message;
                        return $data;
                    }
    
                }

                if (config('settings.wizard_image_vendor') == 'dall-e-2' || config('settings.wizard_image_vendor') == 'dall-e-3' || config('settings.wizard_image_vendor') == 'dall-e-3-hd') {
                    if (isset($response->data)) {
                        foreach ($response->data as $data) {
                            if (isset($data->url)) {
        
                                $curl = curl_init();
                                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                                curl_setopt($curl, CURLOPT_URL, $data->url);
                                $contents = curl_exec($curl);
                                curl_close($curl);
        
        
                                $name = 'wizard-image-' . Str::random(10) . '.png';
        
                                if (config('settings.default_storage') == 'local') {
                                    Storage::disk('public')->put('images/' . $name, $contents);
                                    $image_url = 'images/' . $name;
                                    $storage = 'local';
                                } elseif (config('settings.default_storage') == 'aws') {
                                    Storage::disk('s3')->put('images/' . $name, $contents, 'public');
                                    $image_url = Storage::disk('s3')->url('images/' . $name);
                                    $storage = 'aws';
                                } elseif (config('settings.default_storage') == 'r2') {
                                    Storage::disk('r2')->put('images/' . $name, $contents, 'public');
                                    $image_url = Storage::disk('r2')->url('images/' . $name);
                                    $storage = 'r2';
                                } elseif (config('settings.default_storage') == 'wasabi') {
                                    Storage::disk('wasabi')->put('images/' . $name, $contents);
                                    $image_url = Storage::disk('wasabi')->url('images/' . $name);
                                    $storage = 'wasabi';
                                }
        
                            } else {
                                $data['status'] = 'error';
                                $data['message'] = __('There was an issue with image generation.');
                                return $data; 
                            }                    
                        }
                    } else {
                        $data['status'] = 'error';
                        $data['message'] = __('There was an issue with image generation.');
                        return $data;
                    }
                }
                

            } catch (Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an issue with image generation. ') . $e->getMessage();
                return $data; 
            }

            # Update image credit balance
            $this->updateImageBalance(1, $vendor);

            $flag = Language::where('language_code', $request->language)->first();

            $wizard = ArticleWizard::where('id', $request->wizard)->first();
            $wizard->image_description = $request->image_description;
            $wizard->selected_title = $request->title;
            $wizard->selected_keywords = $request->keywords;
            $wizard->selected_outline = $request->final_outlines;
            $wizard->selected_talking_points = $request->final_talking_points;
            $wizard->language = $flag->language;          
            $wizard->tone = $request->tone;          
            $wizard->creativity = (float)$request->creativity;          
            $wizard->view_point = $request->view_point;  
            $wizard->max_words = $request->words;
            $wizard->additional_information = $request->additional_information;
            //log the additional information to the log file
            \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);
            $wizard->current_step = 4;
            $wizard->save();

            $url = ($storage == 'local') ? URL::asset($image_url) : $image_url;
            return response()->json(['result' => $url]);
           
        }
	}


    /**
	*
	* Prepare article generation
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function prepare(Request $request) 
    {
        if ($request->ajax()) {
            $prompt = '';
            $max_tokens = 50;

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, $max_tokens);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            $flag = Language::where('language_code', $request->language)->first();

            $wizard = ArticleWizard::where('id', $request->wizard)->first();
            $wizard->selected_title = $request->title;
            $wizard->selected_keywords = $request->keywords;
            $wizard->selected_outline = $request->final_outlines;
            $wizard->selected_talking_points = $request->final_talking_points;
            $wizard->image = $request->image_url;
            $wizard->language = $flag->language;
            $wizard->tone = $request->tone;
            $wizard->additional_information = $request->additional_information;
            //log the additional information to the log file
            \Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);
            $wizard->creativity = (float)$request->creativity;
            $wizard->view_point = $request->view_point;
            $wizard->current_step = 5;
            $wizard->save();

            
            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';

            $content = new Content();
            $content->user_id = auth()->user()->id;
            $content->input_text = $prompt;
            $content->language = $request->language;
            $content->language_name = $flag->language;
            $content->language_flag = $flag->language_flag;
            $content->template_code = $request->template;
            $content->template_name = 'Article Wizard';
            $content->icon = '<i class="fa-solid fa-sharp fa-sparkles wizard-icon"></i>';
            $content->group = 'wizard';
            $content->tokens = 0;
            $content->image = $request->image_url;
            $content->plan_type = $plan_type;
            $content->model = $request->model;
            $content->save();

            $data['status'] = 'success';       
            $data['content_id'] = $content->id;
            $data['wizard_id'] = $request->wizard;
            return $data;            

        }
	}


    /**
	*
	* Process Wizard
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {
        # Check Openai APIs
        $key = $this->getOpenai();
        if ($key == 'none') {
            $data['status'] = 'error';
            $data['message'] = __('You must include your personal Openai API key in your profile settings first');
            return $data; 
        }

        
        $model = '';
        $max_tokens = '';
        $internet_access = True;
        #$additional_information = '';
        $wizard = $request->wizard;
        $content = $request->content;
        $max_words = $request->max_words;
        //$additional_information = $request->additional_information;
        //get aditional information from the database
        $additional_information = ArticleWizard::where('id', $wizard)->first()->additional_information;
        #if the request has no data for internet access, set it to true
        if (!isset($request->internet_access)) {
            $internet_access = True;
        } else {
            $internet_access = $request->internet_access;
        }
        $current_content = Content::where('id', $content)->first();
        $model = $current_content->model;


        return response()->stream(function () use($model, $wizard, $content, $internet_access, $additional_information) {

            $text = "";
            $final_text = "";
            $stream_text = "";
            $context = "";

            $input = ArticleWizard::where('id', $wizard)->first();
            #\Log::info('Received additional information: ', ['additional_information' => $request->additional_information]);
            \Log::info('Received additional information: ', ['additional_information' => $additional_information]);
            $outlines = json_decode($input->selected_outline);
            $talking_points = json_decode($input->selected_talking_points);
            $outline_text = '';
            foreach ($outlines as $key => $value) {
                $outline_text .= 'Outline: ' . $value . ' ( Talking points: ';
                foreach ($talking_points as $index => $point) {
                    if ($index == $key) {
                        $points = implode(',', $point);
                        $outline_text .= $points . ') ;';
                    }
                }

            }
            
            try {
                
                //only do this when internet_access is true
                if ($internet_access) {
                    $curl = curl_init();
                                
                    curl_setopt_array($curl, array(
                      CURLOPT_URL => 'https://google.serper.dev/search',
                      CURLOPT_RETURNTRANSFER => true,
                      CURLOPT_ENCODING => '',
                      CURLOPT_MAXREDIRS => 10,
                      CURLOPT_TIMEOUT => 0,
                      CURLOPT_FOLLOWLOCATION => true,
                      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                      CURLOPT_CUSTOMREQUEST => 'POST',
                      CURLOPT_POSTFIELDS =>'{"q":"'.$input->selected_title.'"}',
                      CURLOPT_HTTPHEADER => array(
                        'X-API-KEY: ' . config('services.serper.key'),
                        'Content-Type: application/json'
                      ),
                    ));
                    
                    $search_urls_json = curl_exec($curl);
                
                    //echo 'data: ' . $flag ."\n\n";
                    
                    curl_close($curl);
                
                    $search_urls = json_decode($search_urls_json, true);  // Dekodiert das JSON in ein PHP-Array
                    $links = [];
                    
                    if (isset($search_urls['organic'])) {
                        for ($i = 0; $i < 3; $i++) {
                            if (isset($search_urls['organic'][$i])) {
                                $links[] = $search_urls['organic'][$i]['link'];  // Fügt den Link zum Ergebnisarray hinzu
                            }
                        }
                    }
                    //convert links array to string
                    $links_string = implode(';', $links);
                    //echo the creativity value
                    //echo 'data: ' . $input->creativity ."\n\n";
                
                    $research_content="";
                
                    foreach ($links as $link) {
                        $curl = curl_init();
                    
                        curl_setopt_array($curl, array(
                          CURLOPT_URL => 'https://scrape.serper.dev',
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_ENCODING => '',
                          CURLOPT_MAXREDIRS => 10,
                          CURLOPT_TIMEOUT => 0,
                          CURLOPT_FOLLOWLOCATION => true,
                          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                          CURLOPT_CUSTOMREQUEST => 'POST',
                          CURLOPT_POSTFIELDS =>'{"url":"'.$link.'"}',
                          CURLOPT_HTTPHEADER => array(
                            'X-API-KEY: a40fb3ac4641a0e9d7c9b43f265678f017dcad69',
                            'Content-Type: application/json'
                          ),
                        ));
                    
                        $link_content = curl_exec($curl);
                        #short each link_content to maximum of 500 words
                        $link_content = substr($link_content, 0, 4500);
                    
                        #append link_content to research content including the link itself with : in front
                        $research_content .= $link . ": " . $link_content . " ";
                        //log research content to the log file
                        \Log::info('Research Content: ', ['research_content' => $research_content]);
                        curl_close($curl);
                        //echo 'data: ' . $research_content ."\n\n";
                    }
                    //when research content is not empty short summerize it using OpenAI
                    $sum_research_content = "";
                    if ($research_content != "") {
                        $prompt = "Summarize the following research content: $research_content. Write the summary in the exact same language as the research content. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. Keep the links and cite them as sources.";
                        $response = OpenAI::chat()->create([
                            'model' => $model,
                            'messages' => [[
                                'role' => 'user',
                                'content' => $prompt,
                            ]],
                            'temperature' => (float)$input->creativity,
                        ]);
                        $research_summary = $response['choices'][0]['message']['content'];
                        $sum_research_content .= $research_summary;
                    }
                }

                $subheadings = explode(";", $outline_text); // Assuming each subheading is on a new line
                //delete every empty element from the array
                $subheadings = array_filter($subheadings);
                //log subheadings to the log file
                \Log::info('Subheadings: ', ['subheadings' => $subheadings]);
                $final_article = "";
                if ($internet_access) {
                    $prompt = "As a seasoned SEO professional and blogger, write the introduction paragraph for the following blog post title: $input->selected_title. Do not repeat the title! Important: Write in the exact same language as the blog post title. Use HTML elements such as tables, lists, bullet points, <p>, <bold>, <strong>, <em>, <blockquote>, <mark>, <small>, and <table> only when they enhance the content's readability and value. Use no other HTML! Make the text easy to read and understand. Avoid complicated words and sentences. $input->tone in Unicode. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. Write the article in the view point of $input->view_point person. These are additional information for the text: $additional_information. If possible and only if it makes sense take into account the following reasearch, but don't quote or link to the sources:: $sum_research_content."; 
                //log prompt to the log file
                \Log::info('Prompt: ', ['prompt' => $prompt]);
                }
                else {
                    $prompt = "As a seasoned SEO professional and blogger, write the introduction paragraph for the following blog post title: $input->selected_title. Do not repeat the title! Important: Write in the exact same language as the blog post title. Use HTML elements such as tables, lists, bullet points, <p>, <bold>, <strong>, <em>, <blockquote>, <mark>, <small>, and <table> only when they enhance the content's readability and value. Use no other HTML! Make the text easy to read and understand. Avoid complicated words and sentences. $input->tone in Unicode. Avoid long sentences and complicated words. Aim for a Flesch-Reading-Ease Score of about 70. These are additional information for the text: $additional_information. Write the article in the view point of $input->view_point person.";
                }
                $intro = OpenAI::chat()->create([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                    'temperature' => (float)$input->creativity,
                ]);
                $intro_text = $intro->choices[0]->message->content;
                $final_text .= $intro_text;
                $stream_text .= str_replace(
                    ["\r\n", "\r", "\n", "```html", "```"],
                    [" ", " ", " ", "", ""],
                    $intro_text
                );
                //log intro text to log file
                \Log::info('Intro Text: ', ['intro_text' => $intro_text]);
                $context = $intro_text;

                foreach ($subheadings as $subheading) {
                    $subheading = trim($subheading);
                    //echo "data: " .$additional_information;
                    //when internet
                    if ($internet_access) {
                        $prompt = "As a seasoned SEO professional and blogger, write a paragraph based on this outline subheading: '$subheading'. Do not include an introduction or conclusion, as this is part of a larger article. Enclose the subheading in HTML <H2></H2> tags and remove the word 'Outline'. Aim for a Flesch-Reading-Ease Score of about 70 by avoiding long sentences and complex words. Write from the viewpoint of $input->view_point person. Use HTML elements such as tables, lists, bullet points, <p>, <bold>, <strong>, <em>, <blockquote>, <mark>, <small>, and <table> only when they enhance the content's readability and value. Use no other HTML! Focus on creating high-quality, engaging, and easy-to-understand content that optimizes SEO. $input->tone in Unicode. Here is the content before your section: CONTENT BEFORE START: $context. CONTENT BEFORE END. Important: Do not repeat any of this content or the structure of the content! Always avoid repetations! If relevant and beneficial, incorporate the following research, but don't link to the sources: RESEARCH START: $sum_research_content. RESEARCH END. Only respond with the content.";
                        //log prompt to the log file
                        \Log::info('Prompt: ', ['prompt' => $prompt]);                                                   
                    } else {
                        $prompt = "As a seasoned SEO professional and blogger, write a paragraph based on this outline subheading: '$subheading'. Do not include an introduction or conclusion, as this is part of a larger article. Enclose the subheading in HTML <H2></H2> tags and remove the word 'Outline'. Aim for a Flesch-Reading-Ease Score of about 70 by avoiding long sentences and complex words. Write from the viewpoint of $input->view_point person. Use HTML elements such as tables, lists, bullet points, <p>, <bold>, <strong>, <em>, <blockquote>, <mark>, <small>, and <table> only when they enhance the content's readability and value. Use no other HTML! Focus on creating high-quality, engaging, and easy-to-understand content that optimizes SEO. $input->tone in Unicode. Here is the content before your section: CONTENT BEFORE START: $context. CONTENT BEFORE END. Important: Do not repeat any of this content or the structure of the content! Always avoid repetations!";                          
                    }
                    //if additional_information is not empty append it to the prompt
                    if ($additional_information != "") {
                        $prompt .= "Additional Information: $additional_information";
                    }


                    $results = OpenAI::chat()->createStreamed([
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'frequency_penalty' => 0,
                        'presence_penalty' => 0,
                        'temperature' => (float)$input->creativity,
                    ]);
                    foreach ($results as $result) {
       
                        if (isset($result['choices'][0]['delta']['content'])) {
                            $raw = $result['choices'][0]['delta']['content'];
                            $clean = str_replace(
                                ["\r\n", "\r", "\n", "```html", "```"],
                                [" ", " ", " ", "", ""],
                                $raw
                            );
                            $text .= $raw;
                            $final_text .= $clean;
                            $stream_text .= $clean;
        
                            //echo 'data: ' . $clean ."\n\n";
                            //ob_flush();
                            //flush();
                            //usleep(400);
                        }
        
                        if (connection_aborted()) { break; }
                    }
                    echo 'data: ' . $stream_text ."\n\n";
                    $context = $final_text;
                    $stream_text="";
                }
            //translate the word Sources to the selected language by using $request->language with OpenAI and append it to the final text following the $links line by line from the array.
            if ($internet_access) {
                //get language from the database and store it in $language ArticleWizard wizard id first language
                $language = ArticleWizard::where('id', $wizard)->first()->language;
                //only do this if $languages does not contain English (USA) or English (UK)
                if ($language != 'English (USA)' && $language != 'English (UK)') {
                    //prompt OpenAI to translate the word Sources to the selected language
                    $prompt = "Translate the word Sources to $language. Only answer with the translation of the word. If it is English just answer with Sources:";
                    $response = OpenAI::chat()->create([
                        'model' => $model,
                        'messages' => [[
                            'role' => 'user',
                            'content' => $prompt,
                        ]],
                        'temperature' => (float)$input->creativity,
                    ]);
                    $translation = $response['choices'][0]['message']['content'];
                    echo 'data: ' ."</br>". $translation .":</br>";
                    $final_text .=  $translation . ":</br>";
                    #loop through the links array and append each link to the final text
                }
                else {
                    echo 'data: Sources:</br>';
                    $final_text .= "</br>Sources:</br>";
                }
                #loop through the links array and append each link to the final text
                foreach ($links as $link) {
                    #wrap the links in html with <a href="$link">$link</a>
                    $link = "<a href='$link'>$link</a>";
                    $final_text .= $link . "</br></br>";
                    echo $link ."</br>";
                }
            }
            } catch (\Exception $exception) {
                echo "data: " . $exception->getMessage();
                echo "\n\n";
                ob_flush();
                flush();
                //sleep for 15 seconds
                sleep(15);
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();
                usleep(50000);
            }
            //echo 'data: ' . $final_text ."\n\n";
            // Optionally print the entire article at the end if needed
            // echo $results;            
            
            // Print or further process the result as needed

            # Update credit balance
            $words = count(explode(' ', ($text)));
            HelperService::updateBalance($words, $model); 
            // if ($input->language != 'Chinese (Mandarin)' && $input->language != 'Japanese (Japan)') {
            //     $words = count(explode(' ', ($text)));
            //     $this->updateBalance($words); 
            // } else {
            //     $words = $this->updateBalanceKanji($text);
            // }
             

            $content = Content::where('id', $content)->first();
            $content->tokens = $words;
            $content->words = $words;
            $content->input_text = $prompt;
            $content->result_text = $final_text;
            $content->title = $input->selected_title;
            $content->workbook = $input->workbook;
            $content->save();
            
            echo "\n\n";
            echo 'data: [DONE]';
            echo "\n\n";
            ob_flush();
            flush();
            usleep(40000);
            
            
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
            'X-Accel-Buffering' => 'no',
        ]);

	}


    /**
	*
	* Update user image balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateImageBalance($images, $vendor) {

        $user = User::find(Auth::user()->id);

        if ($vendor == 'dalle') {
            if (auth()->user()->available_dalle_images != -1) {
        
                if (Auth::user()->available_dalle_images > $images) {
    
                    $total_images = Auth::user()->available_dalle_images - $images;
                    $user->available_dalle_images = ($total_images < 0) ? 0 : $total_images;
    
                } elseif (Auth::user()->available_dalle_images_prepaid > $images) {
    
                    $total_images_prepaid = Auth::user()->available_dalle_images_prepaid - $images;
                    $user->available_dalle_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;
    
                } elseif ((Auth::user()->available_dalle_images + Auth::user()->available_dalle_images_prepaid) == $images) {
    
                    $user->available_dalle_images = 0;
                    $user->available_dalle_images_prepaid = 0;
    
                } else {
    
                    if (!is_null(Auth::user()->member_of)) {
    
                        $member = User::where('id', Auth::user()->member_of)->first();
    
                        if ($member->available_dalle_images > $images) {
    
                            $total_images = $member->available_dalle_images - $images;
                            $member->available_dalle_images = ($total_images < 0) ? 0 : $total_images;
                
                        } elseif ($member->available_dalle_images_prepaid > $images) {
                
                            $total_images_prepaid = $member->available_dalle_images_prepaid - $images;
                            $member->available_dalle_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;
                
                        } elseif (($member->available_dalle_images + $member->available_dalle_images_prepaid) == $images) {
                
                            $member->available_dalle_images = 0;
                            $member->available_dalle_images_prepaid = 0;
                
                        } else {
                            $remaining = $images - $member->available_dalle_images;
                            $member->available_dalle_images = 0;
            
                            $prepaid_left = $member->available_dalle_images_prepaid - $remaining;
                            $member->available_dalle_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                        }
    
                        $member->update();
    
                    } else {
                        $remaining = $images - Auth::user()->available_dalle_images;
                        $user->available_dalle_images = 0;
    
                        $prepaid_left = Auth::user()->available_dalle_images_prepaid - $remaining;
                        $user->available_dalle_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
                }
            }
        } else {
            if (auth()->user()->available_sd_images != -1) {
        
                if (Auth::user()->available_sd_images > $images) {
    
                    $total_images = Auth::user()->available_sd_images - $images;
                    $user->available_sd_images = ($total_images < 0) ? 0 : $total_images;
    
                } elseif (Auth::user()->available_sd_images_prepaid > $images) {
    
                    $total_images_prepaid = Auth::user()->available_sd_images_prepaid - $images;
                    $user->available_sd_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;
    
                } elseif ((Auth::user()->available_sd_images + Auth::user()->available_sd_images_prepaid) == $images) {
    
                    $user->available_sd_images = 0;
                    $user->available_sd_images_prepaid = 0;
    
                } else {
    
                    if (!is_null(Auth::user()->member_of)) {
    
                        $member = User::where('id', Auth::user()->member_of)->first();
    
                        if ($member->available_sd_images > $images) {
    
                            $total_images = $member->available_sd_images - $images;
                            $member->available_sd_images = ($total_images < 0) ? 0 : $total_images;
                
                        } elseif ($member->available_sd_images_prepaid > $images) {
                
                            $total_images_prepaid = $member->available_sd_images_prepaid - $images;
                            $member->available_sd_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;
                
                        } elseif (($member->available_sd_images + $member->available_sd_images_prepaid) == $images) {
                
                            $member->available_sd_images = 0;
                            $member->available_sd_images_prepaid = 0;
                
                        } else {
                            $remaining = $images - $member->available_sd_images;
                            $member->available_sd_images = 0;
            
                            $prepaid_left = $member->available_sd_images_prepaid - $remaining;
                            $member->available_sd_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                        }
    
                        $member->update();
    
                    } else {
                        $remaining = $images - Auth::user()->available_sd_images;
                        $user->available_sd_images = 0;
    
                        $prepaid_left = Auth::user()->available_sd_images_prepaid - $remaining;
                        $user->available_sd_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
                }
            }
        }

        $user->update();

    }


    /**
	*
	* Save changes
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function save(Request $request) 
    {
        if ($request->ajax()) {

            $uploading = new UserService();
            $upload = $uploading->upload();
            if (!$upload['status']) return;    

            $document = Content::where('id', request('id'))->first(); 

            if ($document->user_id == Auth::user()->id){

                $document->result_text = $request->text;
                $document->title = $request->title;
                $document->workbook = $request->workbook;
                $document->save();

                $data['status'] = 'success';
                return $data;  
    
            } else{

                $data['status'] = 'error';
                return $data;
            }  
        }
	}


    /**
	*
	* Get openai instance
	* @param - file id in DB
	* @return - confirmation
	*
	*/
    public function getOpenai() 
    {
         # Check personal API keys
         if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                return 'none'; 
            } else {
                config(['openai.api_key' => auth()->user()->personal_openai_key]); 
                return 'valid';
            } 

        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    return 'none'; 
                } else {
                    config(['openai.api_key' => auth()->user()->personal_openai_key]); 
                    return 'valid';
                }
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                   $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                   array_push($api_keys, config('services.openai.key'));
                   $key = array_rand($api_keys, 1);
                   config(['openai.api_key' => $api_keys[$key]]);
                   return 'valid';
               } else {
                    config(['openai.api_key' => config('services.openai.key')]);
                    return 'valid';
               }
           }

        } else {
            if (config('settings.openai_key_usage') !== 'main') {
                $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                array_push($api_keys, config('services.openai.key'));
                $key = array_rand($api_keys, 1);
                config(['openai.api_key' => $api_keys[$key]]);
                return 'valid';
            } else {
                config(['openai.api_key' => config('services.openai.key')]);
                return 'valid';
            }
        }
    }


    public function clear(Request $request)
    {
        ArticleWizard::where('user_id', auth()->user()->id)->delete();
        return response()->json("success");
    }



}
