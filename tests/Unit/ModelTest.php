<?php

run_test('user token rotates and cannot be reused after revoke', function (): void {
    Database::beginTransaction();

    try {
        $userModel = new User();
        $email = 'user-' . bin2hex(random_bytes(6)) . '@example.test';
        $created = $userModel->create([
            'full_name' => 'Security User',
            'email' => $email,
            'password' => password_hash('Password123!', PASSWORD_BCRYPT),
        ]);

        $stored = Database::fetchOne('SELECT token_user FROM users WHERE id = ?', [$created['id']]);
        assert_same(Security::hashAuthToken($created['token_user']), $stored->token_user, 'User token should be stored as hash');
        assert_true((bool)$userModel->findByToken($created['token_user']), 'Fresh user token should authenticate');

        $rotated = $userModel->rotateToken($created['id']);
        assert_true(!$userModel->findByToken($created['token_user']), 'Old user token should fail after rotation');
        assert_true((bool)$userModel->findByToken($rotated), 'Rotated user token should authenticate');

        $userModel->revokeTokenByRawToken($rotated);
        assert_true(!$userModel->findByToken($rotated), 'Revoked user token should no longer authenticate');
    } finally {
        if (Database::inTransaction()) {
            Database::rollback();
        }
    }
});

run_test('admin and employer tokens rotate and revoke independently', function (): void {
    Database::beginTransaction();

    try {
        $accountModel = new Account();
        $adminRole = Database::fetchOne("SELECT id FROM roles WHERE title = 'Admin' AND deleted = 0 LIMIT 1");
        $employerRole = Database::fetchOne("SELECT id FROM roles WHERE title = 'Employer' AND deleted = 0 LIMIT 1");

        assert_true((bool)$adminRole, 'Admin role must exist for token test');
        assert_true((bool)$employerRole, 'Employer role must exist for token test');

        $admin = $accountModel->create([
            'full_name' => 'Security Admin',
            'email' => 'admin-' . bin2hex(random_bytes(6)) . '@example.test',
            'password' => password_hash('Password123!', PASSWORD_BCRYPT),
            'role_id' => (int)$adminRole->id,
        ]);
        $employer = $accountModel->create([
            'full_name' => 'Security Employer',
            'email' => 'employer-' . bin2hex(random_bytes(6)) . '@example.test',
            'password' => password_hash('Password123!', PASSWORD_BCRYPT),
            'role_id' => (int)$employerRole->id,
        ]);

        $adminStored = Database::fetchOne('SELECT token FROM accounts WHERE id = ?', [$admin['id']]);
        $employerStored = Database::fetchOne('SELECT token FROM accounts WHERE id = ?', [$employer['id']]);
        assert_same(Security::hashAuthToken($admin['token']), $adminStored->token, 'Admin token should be stored as hash');
        assert_same(Security::hashAuthToken($employer['token']), $employerStored->token, 'Employer token should be stored as hash');

        $rotatedAdmin = $accountModel->rotateToken($admin['id']);
        $rotatedEmployer = $accountModel->rotateToken($employer['id']);
        assert_true(!$accountModel->findByToken($admin['token']), 'Old admin token should fail after rotation');
        assert_true(!$accountModel->findByToken($employer['token']), 'Old employer token should fail after rotation');
        assert_true((bool)$accountModel->findByToken($rotatedAdmin), 'Rotated admin token should authenticate');
        assert_true((bool)$accountModel->findByToken($rotatedEmployer), 'Rotated employer token should authenticate');

        $accountModel->revokeTokenByRawToken($rotatedAdmin);
        $accountModel->revokeTokenByRawToken($rotatedEmployer);
        assert_true(!$accountModel->findByToken($rotatedAdmin), 'Revoked admin token should no longer authenticate');
        assert_true(!$accountModel->findByToken($rotatedEmployer), 'Revoked employer token should no longer authenticate');
    } finally {
        if (Database::inTransaction()) {
            Database::rollback();
        }
    }
});

run_test('anonymous saved jobs merge into the signed-in user bucket', function (): void {
    Database::beginTransaction();

    try {
        $userModel = new User();
        $savedJobModel = new SavedJob();
        $user = $userModel->create([
            'full_name' => 'Saved Job User',
            'email' => 'saved-' . bin2hex(random_bytes(6)) . '@example.test',
            'password' => password_hash('Password123!', PASSWORD_BCRYPT),
        ]);

        $jobOneSlug = 'saved-job-one-' . bin2hex(random_bytes(4));
        $jobTwoSlug = 'saved-job-two-' . bin2hex(random_bytes(4));
        Database::execute('INSERT INTO jobs (title, slug) VALUES (?, ?)', ['Saved Job One', $jobOneSlug]);
        $jobOneId = (int)Database::lastInsertId();
        Database::execute('INSERT INTO jobs (title, slug) VALUES (?, ?)', ['Saved Job Two', $jobTwoSlug]);
        $jobTwoId = (int)Database::lastInsertId();

        $anonymousSavedJobId = $savedJobModel->create(bin2hex(random_bytes(16)));
        $userSavedJobId = $savedJobModel->create(bin2hex(random_bytes(16)), (int)$user['id']);
        $savedJobModel->addJob($anonymousSavedJobId, $jobOneId);
        $savedJobModel->addJob($userSavedJobId, $jobTwoId);

        $savedJobModel->mergeJobs($anonymousSavedJobId, $userSavedJobId);
        $merged = $savedJobModel->findById($userSavedJobId);
        sort($merged->job_ids);

        assert_same([$jobOneId, $jobTwoId], $merged->job_ids, 'Merged saved jobs should contain anonymous and user items');
    } finally {
        if (Database::inTransaction()) {
            Database::rollback();
        }
    }
});
