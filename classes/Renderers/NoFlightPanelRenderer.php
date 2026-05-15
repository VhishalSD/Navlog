<?php

class NoFlightPanelRenderer
{
    public static function render(): string
    {
        return '
        <div class="database-panel no-flight-panel">
            <strong>No flight selected</strong>
            <span class="panel-inline-info">Create or select a flight before entering aircraft timing and NAVLOG legs.</span>
        </div>';
    }
}
