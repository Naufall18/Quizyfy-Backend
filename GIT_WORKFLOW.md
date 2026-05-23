# 🔧 Git Workflow & Branch Protection Rules

**Status:** ✅ Setup Complete  
**Repository:** quizyfy-backend  
**Owner:** Naufall18

---

## 📋 Branch Strategy

We follow a **feature branch workflow** for better maintenance and code review.

### Branch Naming Convention

```
master                          - Production ready code
develop                         - Integration branch
feature/<feature-name>          - New features
bugfix/<bug-name>              - Bug fixes
docs/<documentation-name>       - Documentation updates
hotfix/<hotfix-name>           - Critical fixes
```

### Examples
```
feature/add-exam-analytics
bugfix/fix-login-error
docs/complete-api-documentation
hotfix/security-patch
```

---

## 🚀 Workflow: How to Push Changes

### Step 1: Create a Feature Branch
```bash
# Update master first
git checkout master
git pull origin master

# Create feature branch
git checkout -b feature/your-feature-name
# or
git checkout -b docs/your-documentation
# or
git checkout -b bugfix/your-bug-fix
```

### Step 2: Make Changes & Commit
```bash
# Make your changes...

# Stage changes
git add .

# Commit with descriptive message
git commit -m "feat: add new endpoint for user analytics

- Add GET /admin/analytics endpoint
- Return user statistics and charts
- Include filtering by date range"
```

### Step 3: Push to Branch
```bash
# Push to feature branch (NOT to master!)
git push origin feature/your-feature-name
```

### Step 4: Create Pull Request
```bash
# Option 1: Using GitHub CLI
gh pr create --title "Add feature X" --body "Description of changes"

# Option 2: Go to GitHub website
# https://github.com/Naufall18/quizyfy-backend
# Click "New Pull Request"
# Select your branch and create PR
```

### Step 5: Wait for Review & Merge
- Someone will review your code
- Make changes if requested
- Once approved, PR gets merged to master

### Step 6: Delete Feature Branch
```bash
# After merge, delete local branch
git branch -d feature/your-feature-name

# Delete remote branch
git push origin --delete feature/your-feature-name
```

---

## ✅ Branch Protection Rules (Master Branch)

The `master` branch is protected. Here's what that means:

### Rule 1: Require Pull Request Review
- ✅ Changes MUST go through Pull Request (PR)
- ✅ Minimum 1 approval required before merge
- ❌ Cannot push directly to master

### Rule 2: Dismiss Stale Reviews
- ✅ If you push new commits, old reviews are dismissed
- Requires re-review before merge

### Rule 3: Enforce Admins
- Even repository owner must follow the rules

---

## 📝 Commit Message Convention

Follow conventional commits for clear history:

```
type(scope): subject

body

footer
```

### Types
- `feat:` - New feature
- `fix:` - Bug fix
- `docs:` - Documentation
- `style:` - Code style (formatting, etc)
- `refactor:` - Code refactoring
- `perf:` - Performance improvement
- `test:` - Adding/updating tests
- `chore:` - Build, dependencies, etc

### Examples
```bash
# Feature
git commit -m "feat(auth): add google oauth login"

# Bug fix
git commit -m "fix(exam): fix timer not counting down"

# Documentation
git commit -m "docs(api): add endpoint documentation"

# Multiple changes
git commit -m "feat(exam): add analytics endpoint

- Add GET /admin/exams/{id}/analytics
- Return score distribution chart
- Include time spent analysis"
```

---

## 🔄 Typical Workflow Scenario

### Scenario: Adding a New API Endpoint

```bash
# 1. Update & create branch
git checkout master
git pull origin master
git checkout -b feature/add-user-stats-endpoint

# 2. Edit files (in your IDE)
# - Edit Controllers/AdminController.php
# - Edit routes/api.php
# - Add request validation

# 3. Test locally
php artisan serve
# Test with Postman

# 4. Commit changes
git add app/Http/Controllers/AdminController.php routes/api.php
git commit -m "feat(admin): add user statistics endpoint

- Add GET /admin/users/stats endpoint
- Return total users, new users this month, etc
- Include filtering by date range"

# 5. Push to branch
git push origin feature/add-user-stats-endpoint

# 6. Create Pull Request
gh pr create --title "Add user statistics endpoint" \
  --body "Adds GET /admin/users/stats endpoint for analytics dashboard"

# 7. Wait for review
# Someone on team will review & test

# 8. Make changes if needed (repeat steps 4-5)

# 9. Get merged to master
# (after approval & passing all checks)
```

---

## 🆘 Common Scenarios

### Scenario 1: I accidentally committed to master

```bash
# Undo the last commit but keep changes
git reset --soft HEAD~1

# Create branch with those changes
git checkout -b feature/my-feature

# Push to branch instead
git push origin feature/my-feature
```

### Scenario 2: I need latest master in my branch

```bash
# Update master
git checkout master
git pull origin master

# Go back to your branch
git checkout feature/my-feature

# Rebase (recommended) or Merge
git rebase master

# Or merge (if rebase is too complex)
git merge master
```

### Scenario 3: I want to see my changes before creating PR

```bash
# Create a draft PR instead
gh pr create --title "WIP: My feature" --draft

# Or mark as draft on GitHub website
# This signals it's not ready for review yet
```

---

## 📊 Protected Branch Rules Summary

| Rule | Status | Details |
|------|--------|---------|
| Require PR | ✅ Yes | Changes must go through Pull Request |
| Minimum Approvals | ✅ 1 | At least 1 approval before merge |
| Dismiss Stale Reviews | ✅ Yes | New commits require re-review |
| Enforce Admins | ✅ Yes | Rules apply to everyone |

---

## ⚠️ What You CANNOT Do on Master

❌ Cannot push directly to master  
❌ Cannot commit without PR  
❌ Cannot merge without approval  
❌ Cannot skip code review  

**Solution:** Use feature branches!

---

## ✅ Best Practices

1. **Keep branches short-lived**
   - Max 1-2 days of work per branch
   - Easier to review and merge

2. **Small, focused PRs**
   - One feature per PR
   - Easier to understand and review

3. **Clear commit messages**
   - Future developers will understand why

4. **Test before pushing**
   - Run tests locally
   - Verify with Postman

5. **Request review when ready**
   - Don't let PRs sit unreviewed

6. **Respond to feedback quickly**
   - Make requested changes promptly
   - Re-request review when done

---

## 🔗 Resources

- [GitHub CLI Documentation](https://cli.github.com/manual)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Git Branching Model](https://nvie.com/posts/a-successful-git-branching-model/)

---

## 📞 Need Help?

If you're unsure:
1. Create a draft PR
2. Ask in team chat
3. Check this documentation
4. Ask repository owner

**Remember:** Better to ask than break things! 🙂
