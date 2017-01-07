<?php
/**
 * Created by PhpStorm.
 * User: michi
 * Date: 29.12.16
 * Time: 15:13
 */

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Screen;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class TodoList
{
    protected $extensions = array('php', 'md', 'txt', 'htm', 'html', 'twig');
    protected $basepath = '';
    protected $cache = '';

    public function __construct($basepath, $cache)
    {
        $this->basepath = $basepath;
        $this->cache = $cache;
    }


    /**
     * @return array
     */
    public function getTodoList()
    {
        $cache = $this->cache;
        $dirs = array('../src', '../app/Resources');

        $cached = $cache->getItem('app.todo.list');
        if (!$cached->isHit()) {
            $r = array();

            foreach ($dirs as $dir) {
                $this->traverseTodoList($dir, $r);
            }

            $cached->set($r);
            $cached->expiresAfter(\DateInterval::createFromDateString('10 minute'));
            $cache->save($cached);
        }

        return $cached->get();
    }

    protected function traverseTodoList($dir, &$todos)
    {
        if ($handle = opendir($this->basepath . '/' . $dir)) {
            while (false !== ($entry = readdir($handle))) {
                // ignore . and ..
                if ($entry == '.' || $entry == '..') continue;

                // get extension
                $i = pathinfo($this->basepath . '/' . $dir . '/' . $entry);

                if (is_dir($this->basepath . '/' . $dir . '/' . $entry)) {
                    $this->traverseTodoList($dir . '/' . $entry, $todos);
                }
                elseif (is_file($dir . '/' . $entry)) {
                    // ignore all extensions except php, htm, html, twig, ...
                    if (!in_array($i['extension'], $this->extensions)) continue;

                    $this->parseFile($dir . '/' . $entry, $todos);
                }

            }
            closedir($handle);
        }
    }

    protected function parseFile($path, &$todos)
    {
        $file = file_get_contents($this->basepath . '/' . $path);
        $matches = array();

        preg_match_all('/(?P<type>TODO|FIXME)[\s:]+(?P<text>.*)$/um', $file, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        foreach ($matches as $match) {
            $type = $match['type'];
            $text = $match['text'];

            $off = $text[1];
            list($before) = str_split($file, $off); // fetches all the text before the match
            $line_number = strlen($before) - strlen(str_replace("\n", "", $before)) + 1;

            $todos[] = new TodoItem($text[0], $path, $line_number, strtolower($type[0]));
        }
    }
}


class TodoItem
{
    protected $type = 'TODO';
    protected $text = '';
    protected $file = '';
    protected $line = 0;

    public function __construct($text, $file = '', $line = 0, $type = 'TODO')
    {
        $this->type = $type;
        $this->text = $text;
        $this->file = $file;
        $this->line = $line;
    }


    public function getType() { return $this->type; }
    public function getText() { return $this->text; }
    public function getFile() { return $this->file; }
    public function getLine() { return $this->line; }
}