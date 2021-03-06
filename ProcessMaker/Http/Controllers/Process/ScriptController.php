<?php

namespace ProcessMaker\Http\Controllers\Process;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use ProcessMaker\Http\Controllers\Controller;
use ProcessMaker\Models\ProcessRequest;
use ProcessMaker\Models\Script;
use ProcessMaker\Models\ScriptCategory;
use ProcessMaker\Models\ScriptExecutor;
use ProcessMaker\Models\User;

class ScriptController extends Controller
{
     /**
     * Get the list of environment variables
     *
     * @return \Illuminate\View\View|\Illuminate\Contracts\View
     */
    public function index(Request $request)
    {
        $catConfig = (object) [
            'labels' => (object) [
                'countColumn' => __('# Scripts'),
            ],
            'routes' => (object) [
                'itemsIndexWeb' => 'scripts.index',
                'editCategoryWeb' => 'script-categories.edit',
                'categoryListApi' => 'api.script_categories.index',
            ],
            'countField' => 'scripts_count',
            'apiListInclude' => 'scriptsCount',
            'permissions' => [
                'view'   => $request->user()->can('view-script-categories'),
                'create' => $request->user()->can('create-script-categories'),
                'edit'   => $request->user()->can('edit-script-categories'),
                'delete' => $request->user()->can('delete-script-categories'),
            ],
        ];

        $listConfig = (object) [
            'scriptExecutors' => ScriptExecutor::list(),
            'countCategories' => ScriptCategory::where(['status' => 'ACTIVE', 'is_system' => false])->count()
        ];

        return view('processes.scripts.index', compact ('listConfig', 'catConfig'));
    }

    public function edit(Script $script, User $users)
    {
        $selectedUser = $script->runAsUser;
        $scriptExecutors = ScriptExecutor::list($script->language);
        $addons = $this->getPluginAddons('edit', compact(['script']));

        return view('processes.scripts.edit', compact('script', 'selectedUser', 'addons', 'scriptExecutors'));
    }

    public function builder(Request $request, Script $script)
    {
        $processRequestAttributes = $this->getProcessRequestAttributes();
        $processRequestAttributes['user_id'] = $request->user()->id;
        
        $testData = [
            '_request' => $processRequestAttributes
        ];
        return view('processes.scripts.builder', compact('script', 'testData'));
    }

    private function getProcessRequestAttributes()
    {
        $emptyProcessRequest = new ProcessRequest();
        $columns = Schema::connection(
            $emptyProcessRequest->getConnectionName()
        )->getColumnListing(
            $emptyProcessRequest->getTable()
        );

        $attributes = [];

        foreach($columns as $column) {
            $attributes[$column] = null;
        }

        return $attributes;
    }
}
