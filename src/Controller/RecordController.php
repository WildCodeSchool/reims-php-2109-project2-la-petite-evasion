<?php

namespace App\Controller;

use App\Model\RecordManager;

class RecordController extends AbstractController
{
/**
     * Show first level
     */
    public function index(): string
    {
        $recordManager = new RecordManager();
        $records = $recordManager->selectAll('TIME');

        return $this->twig->render('Record/leaderboard.html.twig', ['records' => $records]);
    }

}
