<?php
    $output = $properties['inspectedOutput'] ?? [];
?>
<div class="tailor-blueprint">
    <div class="form">
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th class="col-2">Name</th>
                    <td class="col-10"><?= e($blueprintObj->name) ?></td>
                </tr>
                <tr>
                    <th>Handle</th>
                    <td><span title="<?= e($blueprintObj->uuid) ?>"><?= e($blueprintObj->handle) ?></span></td>
                </tr>
                <?php if (isset($output['controllerFile'])): ?>
                    <tr>
                        <th>Controller</th>
                        <td><span>controllers/<?= e(basename($output['controllerFile'])) ?></span></td>
                    </tr>
                <?php endif ?>
                <?php if (isset($output['modelFiles'])): ?>
                    <tr>
                        <th>Models</th>
                        <td>
                            <?php foreach ((array) $output['modelFiles'] as $file): ?>
                                <span class="d-block">models/<?= e(basename($file)) ?></span>
                            <?php endforeach ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if (isset($output['migrationFiles'])): ?>
                    <tr>
                        <th>Migrations</th>
                        <td>
                            <?php foreach ((array) $output['migrationFiles'] as $file): ?>
                                <span class="d-block">updates/<?= e($file) ?></span>
                            <?php endforeach ?>
                        </td>
                    </tr>
                <?php endif ?>
                <?php if (isset($output['errorMessage'])): ?>
                    <tr>
                        <th class="table-danger">Error</th>
                        <td class="table-danger"><?= e($output['errorMessage']) ?></td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>
