<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class AdminPresenter extends Nette\Application\UI\Presenter
{
	/** @var Nette\Database\Context */
	private $database;
	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}
	
	
	public function createComponentHome()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Homepage');
		}
		
		$user = $this->database->table('o2_users')->get($this->getUser()->getIdentity()->getId());				
		$form = new Form;		
		$form->addSelect('template', 'Choose Template', [
				'light' => 'Light',
				'dark' => 'Dark',
			])
			->setHtmlAttribute('class','form-control');
		
		$form->addSubmit('send', 'Submit')
			->setHtmlAttribute('class','btn btn-lg btn-primary btn-block');
		
		$form->setDefaults($user->toArray());
			
		// setup form rendering - Bootstrap!!
		$renderer = $form->getRenderer();
		$renderer->wrappers['controls']['container'] = NULL;
		$renderer->wrappers['pair']['container'] = 'div class=form-group';
		$renderer->wrappers['pair']['.error'] = 'has-error';
		$renderer->wrappers['control']['container'] = 'div class=col-sm-9';
		$renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
		$renderer->wrappers['control']['description'] = 'span class=help-block';
		$renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';

		// make form and controls compatible with Twitter Bootstrap
		$form->getElementPrototype()->class('form-horizontal');

		foreach ($form->getControls() as $control) {
			if ($control instanceof Controls\Button) {
				$control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
				$usedPrimary = TRUE;
			} elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
				$control->getControlPrototype()->addClass('form-control');

			} elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
				$control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
			}
		}
					
		$form->onSuccess[] = [$this, 'homeSucceeded'];
		$this->template->appTemplate = $user->toArray()['template'];
		
		return $form;
	}
	
	public function homeSucceeded(Form $form, \stdClass $values)
	{
		$user = $this->database->table('o2_users')->get($this->getUser()->getIdentity()->getId());
		$user->update($values);

		$this->flashMessage('Template changed', 'success');
		//$this->redirect('this');
	}
}
