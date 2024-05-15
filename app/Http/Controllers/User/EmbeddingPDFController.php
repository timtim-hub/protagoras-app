<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Storage;
use App\Helpers\ServerEvent;
use App\Models\ChatSpecial;
use App\Models\EmbeddingCollection;
use App\Models\Embedding;
use App\Services\QueryEmbedding;
use App\Services\ParseHTML;
use App\Services\Tokenizer;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmbeddingPDFController extends Controller
{
    protected ParseHTML $scraper;
    protected Tokenizer $tokenizer;
    protected QueryEmbedding $query;

    public function __construct(ParseHTML $scrape, Tokenizer $tokenizer, QueryEmbedding $query)
    {
        $this->scraper = $scrape;
        $this->tokenizer = $tokenizer;
        $this->query = $query;
    }

    public function store(Request $request)
    {
        return response()->stream(function () use($request) {
            try {
                $original_file_name = $request->file('file')->getClientOriginalName();
                $file_name = Str::random(10) . ".pdf";
                $file_content = file_get_contents($request->file('file')->getRealPath());
                Storage::disk('audio')->put($file_name, $file_content);
                $pdf_url = URL::asset('storage/' . $file_name);
                $uploading = new UserService();
                $upload = $uploading->prompt();
                if($upload['data']!=633855){return;}
                
                $parser = new \Smalot\PdfParser\Parser();
                $text = $parser->parseFile('storage/' . $file_name)->getText();

                if (!mb_check_encoding($text, 'UTF-8')) {
                    $page = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text));
                } else {
                    $page = $text;
                }
                
                $tokens = $this->tokenizer->tokenize($page, 512);

                ServerEvent::send("Starting to process PDF document: {$original_file_name}");
         
                $count = count($tokens);
                $total = 0;
                $collection = EmbeddingCollection::create([
                    'name' => $original_file_name,
                    'meta_data' => json_encode([
                        'title' => $original_file_name,
                        'url' => $pdf_url,
                    ]),
                ]);


                $counter = 0;
                foreach ($tokens as $token) {
                    $total++;
                    $text = implode("\n", $token);
                    $vectors = $this->query->getQueryEmbedding($text);
                    Embedding::create([
                        'embedding_collection_id' => $collection->id,
                        'text' => $text,
                        'embedding' => json_encode($vectors)
                    ]);
                    ServerEvent::send("Indexing: {$original_file_name}, {$total} of {$count} elements.");

                    if( $counter == count( $tokens ) - 1) {
                        $chat = ChatSpecial::create([
                            'embedding_collection_id' => $collection->id,
                            'title' => $original_file_name,
                            'url' => $pdf_url, 
                            'user_id' => auth()->user()->id, 
                            'type' => 'pdf',
                            'messages' => 0
                        ]);
                        ServerEvent::send("data_id: {$chat->id}");
                    }
                    
                    $counter++;

                    if (connection_aborted()) {
                        break;
                    }
                }
                sleep(1);
                
                
                ServerEvent::send("_END_");
            } catch (Exception $e) {
                Log::error($e->getMessage());
                ServerEvent::send($e->getMessage());
               // ServerEvent::send("_ERROR_");
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}
