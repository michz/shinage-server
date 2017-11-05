<?php

namespace AppBundle\Controller\Api\FilePool;

use AppBundle\Service\FilePool;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class Upload extends Controller
{

    /**
     * @Route("/api/v1/filepool/upload", name="api-filepool-upload")
     */
    public function uploadAction(Request $request)
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

        $succ = (bool) $this->saveFile($folder, $object->filename, $fileContent);


        // @TODO{s:5} Sinnvolle Antwort (JSON)
        var_dump($succ);
        exit;
    }

    protected function getOwnerBase($ownerString)
    {
        $base = $tmb_path = realpath($this->container->getParameter('path_pool')) . '/' .
            str_replace(':', '-', $ownerString);
        return $base;
    }

    protected function saveFile($folder, $filename, $content)
    {
        var_dump($folder);
        FilePool::createPathIfNeeded($folder);

        return file_put_contents($folder . '/' . $filename, $content);
    }
}
