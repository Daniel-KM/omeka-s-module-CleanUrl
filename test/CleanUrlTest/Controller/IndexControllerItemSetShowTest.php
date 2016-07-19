<?php

namespace CleanUrlTest\Controller;

use CleanUrlTest\Controller\CleanUrlControllerTestCase;

class IndexControllerItemSetShowTest extends CleanUrlControllerTestCase
{
    protected function getSettings()
    {
        return [
            'clean_url_identifier_property' => '10',
            'clean_url_identifier_prefix' => '',
            'clean_url_main_path' => '',
            'clean_url_item_set_generic' => 'collection/',
            'clean_url_media_generic' => 'media/',
            'clean_url_media_allowed' => serialize(['generic', 'generic_item', 'item_set', 'item_set_item']),
            'clean_url_item_allowed' => serialize(['generic']),
        ];
    }

    public function testItemSetShowAction()
    {
        $site_slug = $this->site->slug();
        $item_set_identifier = $this->item_set->value('dcterms:identifier');
        $path = "/s/$site_slug/collection/$item_set_identifier";
        $this->dispatch($path);

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('CleanUrl\Controller\Index');
        $this->assertActionName('item-set-show');

        $this->assertQueryContentContains('#content > h2', 'Item Set 1');
    }
}