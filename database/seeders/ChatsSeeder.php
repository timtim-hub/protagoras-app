<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Chat;

class ChatsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $templates = [
            ['id' => 45, 'name' => 'AI Image Generator', 'sub_name' => 'Image Generator', 'chat_code' => 'IMAGE', 'logo' => '/chats/robot.webp', 'status' => true, 'prompt' => '', 'category' => 'all', 'type' => 'original', 'description' => 'Unleash the full potential of AI and effortlessly create a wide range of images within your chat environment. ', 'group' => 'nogroup'],
        ];

        foreach ($templates as $template) {
            Chat::updateOrCreate(['id' => $template['id']], $template);
        }
    }
}
