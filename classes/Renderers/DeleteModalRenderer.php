<?php

class DeleteModalRenderer
{
    public static function renderFlightModal(array $deleteModalView): string
    {
        return '
<div id="delete-flight-modal" class="delete-modal-overlay">
    <div class="delete-modal-box">
        <h2>' . ViewHelper::e($deleteModalView['deleteFlightTitle'] ?? '') . '</h2>
        <p>' . ViewHelper::e($deleteModalView['deleteFlightText'] ?? '') . '</p>

        <div class="delete-modal-actions">
            <button type="button" class="modal-cancel-button" onclick="closeDeleteFlightModal()">Cancel</button>
            <button type="button" class="modal-delete-button" onclick="submitDeleteFlightForm()">Delete flight</button>
        </div>
    </div>
</div>';
    }

    public static function renderLegModal(array $deleteModalView): string
    {
        return '
<div id="delete-leg-modal" class="delete-modal-overlay">
    <div class="delete-modal-box">
        <h2>' . ViewHelper::e($deleteModalView['deleteLegTitle'] ?? '') . '</h2>
        <p>' . ViewHelper::e($deleteModalView['deleteLegText'] ?? '') . '</p>

        <form id="delete-leg-form" method="post" action="' . ViewHelper::e($deleteModalView['deleteLegAction'] ?? 'index.php#table2') . '">
            <input type="hidden" name="action" value="delete_leg">
            <input type="hidden" id="delete_leg_flight_id" name="flight_id" value="">
            <input type="hidden" id="delete_leg_id" name="leg_id" value="">

            <div class="delete-modal-actions">
                <button type="button" class="modal-cancel-button" onclick="closeDeleteLegModal()">Cancel</button>
                <button type="submit" class="modal-delete-button">Delete leg</button>
            </div>
        </form>
    </div>
</div>';
    }
}
