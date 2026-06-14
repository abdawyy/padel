<style>
.player-panel { display: flex; flex-direction: column; gap: 16px; }
.player-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.dark .player-card {
    background: #111827;
    border-color: #374151;
}
.player-empty {
    padding: 48px 20px;
    text-align: center;
    background: #fff;
    border: 2px dashed #d1d5db;
    border-radius: 18px;
    color: #6b7280;
}
.dark .player-empty {
    background: #111827;
    border-color: #374151;
    color: #9ca3af;
}
.player-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.player-filter-btn {
    padding: 6px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    cursor: pointer;
}
.player-filter-btn.active {
    background: rgb(var(--primary-600, 217 119 6) / 1);
    border-color: transparent;
    color: #fff;
}
.dark .player-filter-btn {
    background: #1f2937;
    border-color: #374151;
    color: #e5e7eb;
}
.dark .player-filter-btn.active {
    color: #fff;
}
</style>
