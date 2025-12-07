DELETE FROM assignment_submissions WHERE assignment_id IN (SELECT id FROM assignments WHERE instructions LIKE "QA Smoke%" );
DELETE FROM assignments WHERE instructions LIKE "QA Smoke%";
INSERT INTO assignments (
    lesson_id,
    instructions,
    due_at,
    max_points,
    passing_score,
    requires_approval,
    rubric,
    created_at,
    updated_at
) VALUES (
    1,
    'QA Smoke Assignment: graba un pitch de 2 minutos explicando el objetivo de la leccion.',
    '2025-12-05 23:59:00',
    100,
    70,
    1,
    '{"criteria":[{"label":"Claridad","weight":0.5},{"label":"Pronunciacion","weight":0.5}]}',
    NOW(),
    NOW()
);
SET @assignment_id = LAST_INSERT_ID();
INSERT INTO assignment_submissions (
    assignment_id,
    user_id,
    body,
    attachment_url,
    status,
    score,
    max_points,
    feedback,
    rubric_scores,
    submitted_at,
    created_at,
    updated_at
) VALUES (
    @assignment_id,
    2,
    'Subida QA automatica: enlace a Loom y checklist.',
    'https://loom.com/share/qa-assignment-demo',
    'submitted',
    0,
    100,
    NULL,
    '{"Claridad":0,"Pronunciacion":0}',
    NOW(),
    NOW(),
    NOW()
);
