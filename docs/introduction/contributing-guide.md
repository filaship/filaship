# Contribution Guide

Thank you for your interest in contributing to our project! Whether you're a seasoned developer or just getting started, your contributions are welcome and valued. This guide will walk you through the contribution process step by step.

## Getting Started

### Types of Contributions
We welcome various types of contributions:
- 🐛 Bug fixes
- ✨ New features
- 📚 Documentation improvements
- 🧪 Tests and quality improvements
- 🔧 Performance optimizations

## 📋 How to Contribute

### 1. Set Up Your Environment

**Fork the Repository**
Click the "Fork" button on the top right of the repository page to create your own copy of the project.

**Clone Your Fork**
```bash
git clone https://github.com/YOUR_USERNAME/filaship.git
cd filaship
```

### 2. Development Workflow

**Sync with Main Repository**
```bash
git fetch upstream
git checkout main
git merge upstream/main
```

**Create a Feature Branch**
```bash
# Use descriptive branch names with prefixes:
git checkout -b feature/your-feature-name
git checkout -b fix/bug-description
git checkout -b docs/documentation-update
```

**Make Your Changes**
- Keep commits focused and atomic
- Follow the project's coding standards
- Test your changes thoroughly

### 3. Quality Assurance

**Test Your Changes**
```bash
composer run test
```

**Code Quality Checklist**
- [ ] Code follows project conventions
- [ ] No syntax errors or warnings
- [ ] Removed unnecessary commented code
- [ ] Added appropriate error handling

### 4. Documentation

**Update Documentation**
If your changes include:
- **New features** → Update README and relevant documentation
- **API changes** → Document new endpoints or parameters
- **Configuration changes** → Update installation guides and examples

### 5. Commit Your Changes

**Write Clear Commit Messages**
```bash
# Use imperative mood and be descriptive
git add .
git commit -m "feat: add email notification system"
git commit -m "fix: resolve login form validation error"
git commit -m "docs: update installation instructions"
```

**Push to Your Fork**
```bash
git push origin feature/your-feature-name
```

### 6. Create a Pull Request

**Submit Your Pull Request**
1. Navigate to your fork on GitHub
2. Click "Compare & pull request"
3. Fill out the PR template with:
   - **Clear title**: Briefly describe the change
   - **Detailed description**: Explain what was changed and why
   - **Testing instructions**: How to test your changes

## 📝 Guidelines and Standards

### Code Style
- Follow PSR (PHP Standards Recommendations)
- Use meaningful variable and function names
- Keep functions small and focused
- Add comments for complex logic
- Maintain consistent indentation

### Commit Message Format
Follow [Conventional Commits](https://www.conventionalcommits.org/) specification:
- `feat:` new feature
- `fix:` bug fix
- `docs:` documentation changes
- `style:` formatting, no logic changes
- `refactor:` code refactoring
- `test:` adding or updating tests
- `chore:` maintenance tasks

## 🤝 Community and Support

### Need Help?
- 💬 Open a discussion for questions
- 🐛 Create an issue for bugs
- 💡 Share ideas in feature requests
- 📧 Contact maintainers for urgent matters

### Review Process
1. **Automated checks**: CI/CD pipeline runs tests
2. **Code review**: Maintainers review your changes
3. **Feedback**: Address any requested changes
4. **Merge**: Once approved, your PR will be merged

## 🎉 After Your Contribution

### What Happens Next?
- Your changes will be reviewed by maintainers
- You may receive feedback or requests for modifications
- Once approved, your contribution will be merged
- You'll be credited as a contributor to the project

---

**Thank you for contributing to our project! Your efforts help make this project better for everyone.** 🚀