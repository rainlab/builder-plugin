
    public function index()
    {
        $model = $this->formCreateModelObject()->first();

        if (!$model) {
            $model = $this->formCreateModelObject();
            $model->forceSave();
        }

        return Backend::redirect("{{ controllerUrl }}/update/{$model->id}");
    }