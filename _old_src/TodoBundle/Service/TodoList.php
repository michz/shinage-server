<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace mztx\TodoBundle\Service;

use mztx\TodoBundle\Entity\TodoItem;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class TodoList
{
    /** @var string[]|array */
    protected $extensions = ['php', 'md', 'txt', 'htm', 'html', 'twig'];

    /** @var string */
    protected $basepath = '';

    /** @var AdapterInterface */
    protected $cache;

    public function __construct(string $basepath, AdapterInterface $cache)
    {
        $this->basepath = $basepath;
        $this->cache = $cache;
    }

    /**
     * @return TodoItem[]|array
     */
    public function getTodoList(): array
    {
        $cache = $this->cache;
        $dirs = ['../src', '../app/Resources'];

        $cached = $cache->getItem('app.todo.list');
        if (!$cached->isHit()) {
            $r = [];

            foreach ($dirs as $dir) {
                $this->traverseTodoList($dir, $r);
            }

            $cached->set($r);
            $cached->expiresAfter(\DateInterval::createFromDateString('10 minute'));
            $cache->save($cached);
        }

        $arr = $cached->get();
        $this->sortBySeverity($arr);
        return $arr;
    }

    /**
     * @param TodoItem[]|array $arr
     */
    protected function sortBySeverity(array &$arr): void
    {
        \usort($arr, [$this, 'sortHelper']);
    }

    protected function sortHelper(TodoItem $a, TodoItem $b): int
    {
        return $b->getSeverity() - $a->getSeverity();
    }

    /**
     * @param TodoItem[]|array $todos
     */
    protected function traverseTodoList(string $dir, array &$todos): void
    {
        if ($handle = \opendir($this->basepath . '/' . $dir)) {
            while (false !== ($entry = \readdir($handle))) {
                // ignore . and ..
                if ('.' == $entry || '..' == $entry) {
                    continue;
                }

                // get extension
                $i = \pathinfo($this->basepath . '/' . $dir . '/' . $entry);

                if (\is_dir($this->basepath . '/' . $dir . '/' . $entry)) {
                    $this->traverseTodoList($dir . '/' . $entry, $todos);
                } elseif (\is_file($dir . '/' . $entry)) {
                    // ignore all extensions except php, htm, html, twig, ...
                    if (false === isset($i['extension']) || false === \in_array($i['extension'], $this->extensions)) {
                        continue;
                    }

                    $this->parseFile($dir . '/' . $entry, $todos);
                }
            }
            \closedir($handle);
        }
    }

    /**
     * @param TodoItem[]|array $todos
     */
    protected function parseFile(string $path, array &$todos): void
    {
        $file = \file_get_contents($this->basepath . '/' . $path);

        $matches = [];
        \preg_match_all(
            '/(?P<type>TODO|FIXME)\{(?P<opt>[a-zA-Z0-9,:]*)\}[\s:]+(?P<text>.*)$/um',
            $file,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );
        $this->processMatches($matches, $todos, $file, $path);

        $matches = [];
        \preg_match_all(
            '/(?P<type>TODO|FIXME)[\s:]+(?P<text>.*)$/um',
            $file,
            $matches,
            PREG_SET_ORDER | PREG_OFFSET_CAPTURE
        );
        $this->processMatches($matches, $todos, $file, $path);
    }

    /**
     * @param mixed[]|array    $matches
     * @param TodoItem[]|array $todos
     *
     * @return mixed[]|array
     */
    protected function processMatches(array $matches, array &$todos, string $file, string $path): array
    {
        $r = [];

        foreach ($matches as $match) {
            $opt  = isset($match['opt']) ? $match['opt'] : [''];
            $type = isset($match['type']) ? $match['type'] : [''];
            $text = isset($match['text']) ? $match['text'] : [''];

            $options = \explode(',', $opt[0]);
            $severity = 0;
            foreach ($options as $option) {
                $a = \explode(':', $option);
                if ('s' == $a[0]) {
                    $severity = \intval($a[1]);
                }
            }

            $off = $text[1];
            list($before) = \str_split($file, $off); // fetches all the text before the match
            $line_number = \strlen($before) - \strlen(\str_replace("\n", '', $before)) + 1;

            $text[0] = \strip_tags($text[0]);
            $text[0] = \str_replace(['#}', '{#'], '', $text[0]);

            $todos[] = new TodoItem($text[0], $path, $line_number, \strtolower($type[0]), $severity);
        }

        return $r;
    }
}
