<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Services\Admin\TasksService;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller as BaseController;

class TasksController extends BaseController
{
    public function __construct(private readonly TasksService $tasksService)
    {
    }

    public function __invoke(): View
    {
        return view('admin.tasks', ['tasks_list' => $this->tasksService->getTasks()]);
    }
}
