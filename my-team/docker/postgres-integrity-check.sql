\set ON_ERROR_STOP on

BEGIN;

INSERT INTO users (name, email, password, created_at, updated_at)
VALUES
    ('Audit Coach', 'audit-coach@example.test', 'not-a-real-password', NOW(), NOW()),
    ('Audit Player', 'audit-player@example.test', 'not-a-real-password', NOW(), NOW());

INSERT INTO teams (name, coach_id, created_at, updated_at)
SELECT 'Audit Team', id, NOW(), NOW()
FROM users
WHERE email = 'audit-coach@example.test';

INSERT INTO conversations (type, team_id, created_by, subject, created_at, updated_at)
SELECT 'team_group', teams.id, users.id, 'Audit Team', NOW(), NOW()
FROM teams
JOIN users ON users.email = 'audit-coach@example.test'
WHERE teams.name = 'Audit Team';

DO $integrity$
BEGIN
    BEGIN
        INSERT INTO conversations (type, team_id, created_by, subject, created_at, updated_at)
        SELECT 'team_group', teams.id, users.id, 'Duplicate', NOW(), NOW()
        FROM teams
        JOIN users ON users.email = 'audit-coach@example.test'
        WHERE teams.name = 'Audit Team';
        RAISE EXCEPTION 'team group duplicate was accepted';
    EXCEPTION
        WHEN unique_violation THEN NULL;
    END;

    BEGIN
        INSERT INTO conversations (type, team_id, created_by, subject, created_at, updated_at)
        SELECT 'coach_player_private', teams.id, coach.id, 'Missing private key', NOW(), NOW()
        FROM teams
        JOIN users coach ON coach.email = 'audit-coach@example.test'
        WHERE teams.name = 'Audit Team';
        RAISE EXCEPTION 'private conversation without player key was accepted';
    EXCEPTION
        WHEN raise_exception THEN
            IF SQLERRM <> 'invalid private conversation key' THEN
                RAISE;
            END IF;
    END;
END
$integrity$;

INSERT INTO conversations (type, team_id, created_by, private_player_id, subject, created_at, updated_at)
SELECT 'coach_player_private', teams.id, coach.id, player.id, 'Audit Private', NOW(), NOW()
FROM teams
JOIN users coach ON coach.email = 'audit-coach@example.test'
JOIN users player ON player.email = 'audit-player@example.test'
WHERE teams.name = 'Audit Team';

DO $integrity$
BEGIN
    BEGIN
        INSERT INTO conversations (type, team_id, created_by, private_player_id, subject, created_at, updated_at)
        SELECT 'coach_player_private', teams.id, coach.id, player.id, 'Duplicate private', NOW(), NOW()
        FROM teams
        JOIN users coach ON coach.email = 'audit-coach@example.test'
        JOIN users player ON player.email = 'audit-player@example.test'
        WHERE teams.name = 'Audit Team';
        RAISE EXCEPTION 'private pair duplicate was accepted';
    EXCEPTION
        WHEN unique_violation THEN NULL;
    END;
END
$integrity$;

ROLLBACK;

SELECT 'PostgreSQL chat indexes and trigger constraints verified.' AS result;
