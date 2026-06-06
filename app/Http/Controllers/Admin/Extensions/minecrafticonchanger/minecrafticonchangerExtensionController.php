<?php

namespace Pterodactyl\Http\Controllers\Admin\Extensions\minecrafticonchanger;

use Illuminate\View\View;
use Illuminate\View\Factory as ViewFactory;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\AdminFormRequest;
use Pterodactyl\BlueprintFramework\Libraries\ExtensionLibrary\Admin\BlueprintAdminLibrary;

class minecrafticonchangerExtensionController extends Controller
{
  public function __construct(
    private ViewFactory $view,
    private BlueprintAdminLibrary $blueprint,
  ) {}

  public function index(): View
	{
		return $this->view->make(
			'admin.extensions.minecrafticonchanger.index', [
				'root' => '/admin/extensions/minecrafticonchanger',
				'blueprint' => $this->blueprint,
				'eggs' => json_decode($this->blueprint->dbGet('minecrafticonchanger', 'eggs') ?: '[]'),
			]
		);
	}

	public function post(minecrafticonchangerSettingsFormRequest $request): View
	{
		$this->blueprint->notify('Applied new settings');

		$this->blueprint->dbSet('minecrafticonchanger', 'eggs', json_encode($request->input('eggs')));

		return $this->view->make(
			'admin.extensions.minecrafticonchanger.index', [
				'root' => '/admin/extensions/minecrafticonchanger',
				'blueprint' => $this->blueprint,
				'eggs' => json_decode($this->blueprint->dbGet('minecrafticonchanger', 'eggs') ?: '[]'),
			]
		);
	}
}

class minecrafticonchangerSettingsFormRequest extends AdminFormRequest
{
  public function rules(): array
  {
    return [
      'eggs' => 'required|array',
      'eggs.*' => 'integer',
    ];
  }
}