<?php
namespace AppBundle;

use AppBundle\Service\TemplateManager;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TemplateManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  TemplateManager $testSubject */
    protected $testSubject;

    /** @var  vfsStreamDirectory */
    protected $vfs;

    protected function setUp()
    {
        $this->createVirtualFilesystem();
        $this->testSubject = new TemplateManager($this->vfs->url());
    }

    protected function createVirtualFilesystem()
    {
        $structure = [
            'templates' => [
                'test-slideshow-1' => [
                    'template.php' => '<?php '
                ]
            ]
        ];
        $this->vfs = vfsStream::setup('testroot', null, $structure);
    }

    protected function tearDown()
    {
    }


    public function testGetTemplates()
    {
        //@TODO
    }
}
