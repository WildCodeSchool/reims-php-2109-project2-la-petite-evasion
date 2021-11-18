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
            if (GameController::getGameState() !== GameController::GAME_STATE_FINISHED) {
                $errors[] = "Le niveau n'est pas terminé";
            }

            if (!$errors) {
                $record = [
                    'name' => $_POST['name'],
                    'time' => GameController::getFinishInterval(),
                    'level_id' => GameController::getGameLevelId(),
                ];

                $recordManager = new RecordManager();
                $recordManager->insert($record);
            }
        }
    }

    public function index(): string
    {
        session_start();
        $this->insertRecord();
        GameController::timeRecorded();
        $recordManager = new RecordManager();
        $records = $recordManager->selectRecords();

        return $this->twig->render('Record/leaderboard.html.twig', ['records' => $records,]);
    }
}
