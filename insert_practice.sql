DELETE FROM discord_practices WHERE title = 'QA Planner Test';
INSERT INTO discord_practices (
    lesson_id, cohort_label, practice_package_id, created_by, type,
    title, description, start_at, end_at, duration_minutes, capacity,
    discord_channel_url, status, requires_package, created_at, updated_at
) VALUES (
    1, 'QA Cohort', NULL, 2, 'cohort',
    'QA Planner Test', 'Prueba automatica planner',
    '2025-12-02 16:00:00', '2025-12-02 17:00:00', 60, 8,
    'https://discord.gg/example', 'scheduled', 0, NOW(), NOW()
);
