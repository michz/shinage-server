<?php
declare(strict_types=1);

/*
 * Copyright 2018 by Michael Zapf.
 * Licensed under MIT. See file /LICENSE.
 */

namespace AppBundle\Controller\Api\FilePool;

use AppBundle\Service\FilePool;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class Upload extends Controller
{
    public function uploadAction(Request $request): void
    {
        $request->setRequestFormat('json');

        // get and parse raw data from request
        $raw = $request->getContent();
        // @TODO{s:5} JSON-Fehler abfangen und BadResponse-Fehler zurückgeben
        $object = json_decode($raw);

        // @TODO{s:3} JSON zu vordefinierter Klasse parsen/zuordnen

        // @TODO{s:5} Prüfen, ob Api-Key auf den Ordner schreiben darf
        $folder = $this->getOwnerBase($object->auth->owner) . $object->folder;

        // decode file
        $fileContent = base64_decode($object->filecontent);

        /*$succ = (bool)*/ $this->saveFile($folder, $object->filename, $fileContent);

        // @TODO{s:5} Sinnvolle Antwort (JSON)
        exit;
    }

    protected function getOwnerBase(string $ownerString): string
    {
        return  realpath($this->container->getParameter('path_pool')) . '/' .
            str_replace(':', '-', $ownerString);
    }

    protected function saveFile(string $folder, string $filename, string $content): bool
    {
        FilePool::createPathIfNeeded($folder);

        return (bool) file_put_contents($folder . '/' . $filename, $content);
    }
}
