# 🔒 Branch Protection Setup Guide

**Status:** Guide for setting up branch protection rules  
**Repository:** Quizyfy-Backend  
**Date:** May 2026

---

## 🎯 Overview

Branch protection rules ensure code quality and prevent accidental pushes to master branch.

**Current Status:**
- ✅ PR #1 created with documentation
- ⏳ Awaiting approval to merge
- 🔒 Master branch should be protected before merging

---

## 📋 Branch Protection Requirements

### GitHub Free Plan Limitation ⚠️
GitHub's branch protection feature requires **GitHub Pro** for private repositories.

### Options:

#### Option 1: Upgrade to GitHub Pro (Recommended)
- $4/month per user
- Unlimited private repos
- Full branch protection
- Better collaboration features

#### Option 2: Make Repository Public
- Anyone can see the code
- Free branch protection available
- Good for open source
- Trade-off: Code is public

#### Option 3: Use Free Plan Features
- Use code review process manually
- PR required before merge (manually enforced)
- Clear git workflow in documentation

---

## 🔒 Setting Up Branch Protection (If GitHub Pro)

### Via GitHub Web UI:

1. **Go to Repository Settings**
   ```
   https://github.com/Naufall18/Quizyfy-Backend/settings
   ```

2. **Click "Branches"** (left sidebar)

3. **Add Rule**
   - Click "Add rule"
   - Branch name pattern: `master`

4. **Configure Rules:**
   ```
   ✅ Require a pull request before merging
      └─ Require 1 approval
   ✅ Dismiss stale pull request approvals when new commits are pushed
   ✅ Require code owner reviews (optional)
   ✅ Require status checks to pass before merging
      └─ Require branches to be up to date before merging
   ✅ Include administrators
   ```

5. **Save Changes**

### Via GitHub CLI:

```bash
gh api repos/Naufall18/Quizyfy-Backend/branches/master/protection \
  -X PUT \
  -f enforce_admins=true \
  -f required_status_checks='{"context":[],"strict":true}' \
  -f required_pull_request_reviews='{
    "dismiss_stale_reviews":true,
    "require_code_owner_reviews":false,
    "required_approving_review_count":1
  }' \
  -f restrictions=null
```

---

## ✅ Enforced Workflow (Without Branch Protection)

While waiting for GitHub Pro or if using free plan, enforce this manually:

### Master Branch Rules (Documented in GIT_WORKFLOW.md)

1. **No Direct Pushes**
   ```bash
   # ❌ This is NOT allowed:
   git push origin master
   
   # ✅ This is correct:
   git push origin feature/my-feature
   ```

2. **Pull Request Required**
   - All changes go through PR
   - At least 1 person must review
   - PR creator cannot merge own PR

3. **Commit Message Convention**
   - Follow conventional commits
   - Clear message = easier review

4. **Code Review Process**
   - Author: Submit PR with description
   - Reviewer: Review code & tests
   - Feedback: Author makes changes
   - Approval: Reviewer approves
   - Merge: Maintainer merges to master

---

## 📝 PR Template for Reviews

When creating PR, include:

```markdown
## Description
Brief description of changes

## Changes Made
- List each change
- Clear bullet points

## Testing
How did you test this?
- Manual testing steps
- Test cases

## Related Issues
- Closes #123 (if applicable)

## Screenshots (if UI changes)
- Before/after screenshots

## Checklist
- [ ] Code follows style guidelines
- [ ] Self-reviewed own code
- [ ] Commented complex logic
- [ ] No new warnings generated
- [ ] Added/updated documentation
- [ ] Tested locally
```

---

## 👥 Code Review Checklist

When reviewing PR, check:

```
Code Quality:
☐ Code is readable & well-structured
☐ No code smells or anti-patterns
☐ Error handling is proper
☐ Security issues resolved

Testing:
☐ Sufficient test coverage
☐ All tests pass
☐ Edge cases handled

Documentation:
☐ Code comments are clear
☐ README updated (if needed)
☐ API endpoints documented (if added)

Best Practices:
☐ Follows project conventions
☐ Commit messages are clear
☐ Branch name is descriptive
☐ No merge conflicts
```

---

## 🚀 Current Workflow (Until Branch Protection Set Up)

### Step 1: Create Feature Branch
```bash
git checkout master
git pull origin master
git checkout -b feature/my-feature
```

### Step 2: Make Changes
```bash
# Edit files...
git add .
git commit -m "feat: description"
git push origin feature/my-feature
```

### Step 3: Create PR
```bash
# Via CLI
gh pr create --title "Feature title" --body "Description"

# Via GitHub Web UI
# https://github.com/Naufall18/Quizyfy-Backend/pulls
```

### Step 4: Code Review
- Assign reviewer
- Wait for feedback
- Make changes if requested
- Request re-review

### Step 5: Merge
- Once approved
- Repository owner merges via GitHub UI
- Delete feature branch

---

## 📊 Recommended Review Strategy

### For Documentation PRs
- ✅ Quick review (check clarity)
- ⏱️ 24 hours to review

### For Code PRs
- ✅ Thorough review (check logic, tests)
- ⏱️ 24-48 hours to review

### For Hotfixes
- ✅ Expedited review (critical bugs only)
- ⏱️ Same day review

---

## 🔄 Future: Auto-Merge with GitHub Actions

When workflow mature, can setup:

```yaml
# .github/workflows/auto-merge.yml
name: Auto Merge Approved PRs

on:
  pull_request_review:
    types: [submitted]

jobs:
  auto-merge:
    runs-on: ubuntu-latest
    if: github.event.review.state == 'APPROVED'
    steps:
      - uses: pascalgn/automerge-action@v0.15.6
        env:
          GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

---

## ✨ Benefits of This Workflow

✅ **Code Quality**
- Everyone reviews code
- Catch bugs before merge
- Share knowledge

✅ **Accountability**
- Every change tracked
- Clear audit trail
- Know who changed what

✅ **Collaboration**
- Team learns from reviews
- Best practices shared
- Mentoring opportunity

✅ **Stability**
- Master always stable
- No accidental breaks
- Easy rollback if needed

---

## 📚 Resources

- [GitHub Branch Protection Docs](https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches)
- [GitHub Pro Pricing](https://github.com/pricing)
- [Pull Request Best Practices](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests)

---

## 📞 Questions?

Refer to:
- **GIT_WORKFLOW.md** - Git workflow guide
- **API_DOCUMENTATION.md** - API reference
- **GitHub CLI Help** - `gh --help`

---

**Remember:** The goal is **quality over speed**. Better to review thoroughly than to rush code! 🎯
