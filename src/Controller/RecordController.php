<?php

namespace App\Controller;

use App\Model\RecordManager;

class RecordController extends AbstractController
{
    /**
     * Show first level
     */
    private function insertRecord()
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['name']) || strlen($_POST['name']) > 30) {
                $errors[] = "Ton nom ne peut pas être vide";
            }

            if (!$errors) {
                $recordName = $_POST['name'];
                $recordManager = new RecordManager();
                $recordManager->insert($recordName);
            }
        }
    }
    public function index(): string
    {
        $this->insertRecord();
        $recordManager = new RecordManager();
        $records = $recordManager->selectAll('TIME');

        return $this->twig->render('Record/leaderboard.html.twig', ['records' => $records,]);
    }
}
