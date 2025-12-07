SELECT id, lesson_id, due_at FROM assignments WHERE instructions LIKE 'QA Smoke Assignment%' ORDER BY id DESC LIMIT 1;
