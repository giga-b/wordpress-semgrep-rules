---
name: 🔧 Rule Enhancement
about: Suggest improvements to existing WordPress Semgrep security rules
title: "[RULE] "
labels: ["enhancement", "rules", "needs-triage"]
assignees: []
---

## 🔧 Rule Enhancement Description

**Which rule needs enhancement:**

<!-- Specify the rule ID and file path -->

- **Rule ID**: [e.g., `wordpress.security.nonce-verification`]
- **Rule File**: [e.g., `packs/wp-core-security/nonce-verification.yaml`]
- **Current Severity**: [e.g., ERROR, WARNING, INFO]

## 🎯 Enhancement Type

**What type of enhancement is needed:**

- [ ] **False Positive Reduction** - Rule is flagging safe code
- [ ] **False Negative Reduction** - Rule is missing vulnerable code
- [ ] **Performance Improvement** - Rule is too slow
- [ ] **Coverage Expansion** - Rule needs to cover more patterns
- [ ] **Message Clarity** - Error messages need improvement
- [ ] **Documentation** - Rule needs better documentation
- [ ] **Testing** - Rule needs more test cases

## 📊 Current Behavior

**Describe the current rule behavior:**

<!-- Explain how the rule currently works and what issues exist -->

### Current Pattern
```yaml
# Current rule pattern (if known)
patterns:
  - pattern: "current pattern"
```

### Current Issues
- [ ] **Issue 1**: [Description]
- [ ] **Issue 2**: [Description]
- [ ] **Issue 3**: [Description]

## 🚀 Proposed Enhancement

**Describe the desired improvement:**

<!-- Explain what changes you'd like to see -->

### Enhanced Pattern
```yaml
# Proposed enhanced pattern
patterns:
  - pattern: "enhanced pattern"
  - pattern-not: "safe pattern"
```

### Expected Improvements
- [ ] **Improvement 1**: [Description]
- [ ] **Improvement 2**: [Description]
- [ ] **Improvement 3**: [Description]

## 🔍 Test Cases

### Current False Positives (if applicable)
```php
// Code that incorrectly triggers the rule
<?php
// Safe code that shouldn't trigger the rule
?>
```

### Current False Negatives (if applicable)
```php
// Vulnerable code that should trigger the rule
<?php
// Vulnerable code that the rule misses
?>
```

### Proposed Test Cases
```php
// New test cases to validate the enhancement
<?php
// Test case 1
?>
```

## 📈 Impact Assessment

**How would this enhancement benefit users:**

- [ ] **High Impact** - Would significantly improve rule accuracy
- [ ] **Medium Impact** - Would improve developer experience
- [ ] **Low Impact** - Would provide minor improvements

**Affected user groups:**

- [ ] **WordPress Plugin Developers**
- [ ] **WordPress Theme Developers**
- [ ] **Security Researchers**
- [ ] **Development Teams**

## 🔧 Implementation Details

**Technical considerations for the enhancement:**

### Pattern Complexity
- [ ] **Simple** - Minor pattern adjustment
- [ ] **Moderate** - Pattern restructuring needed
- [ ] **Complex** - Requires new Semgrep features

### Performance Impact
- [ ] **No Impact** - Same or better performance
- [ ] **Minor Impact** - Slight performance decrease acceptable
- [ ] **Major Impact** - Significant performance consideration needed

### Backward Compatibility
- [ ] **Fully Compatible** - No breaking changes
- [ ] **Partially Compatible** - Some existing usage may be affected
- [ ] **Breaking Change** - Requires migration guide

## 📋 Requirements

### Functional Requirements
- [ ] **Requirement 1**: [Description]
- [ ] **Requirement 2**: [Description]
- [ ] **Requirement 3**: [Description]

### Quality Requirements
- [ ] **Test Coverage**: [e.g., Must have 90%+ test coverage]
- [ ] **Performance**: [e.g., Must complete within 5 seconds]
- [ ] **Documentation**: [e.g., Must include clear examples]

## 🧪 Testing Strategy

**How should this enhancement be tested:**

### Automated Testing
- [ ] **Unit Tests** - Test individual pattern components
- [ ] **Integration Tests** - Test rule in context
- [ ] **Regression Tests** - Ensure no existing functionality breaks
- [ ] **Performance Tests** - Measure performance impact

### Manual Testing
- [ ] **WordPress Core** - Test against WordPress core code
- [ ] **Popular Plugins** - Test against popular WordPress plugins
- [ ] **Real-world Examples** - Test against real vulnerability examples

## 📚 Documentation Updates

**What documentation needs to be updated:**

- [ ] **Rule Documentation** - Update rule-specific docs
- [ ] **Examples** - Add new examples
- [ ] **Migration Guide** - If breaking changes
- [ ] **Best Practices** - Update best practice guides

## 🎯 Priority Assessment

**How urgent is this enhancement:**

- [ ] **Critical** - Rule is causing significant issues
- [ ] **High** - Important for next release
- [ ] **Medium** - Nice to have for current release
- [ ] **Low** - Future consideration

## 🔗 Related Issues

**Link to any related issues or discussions:**

<!-- Reference existing issues, PRs, or discussions -->

---

## 📋 Checklist

Before submitting this rule enhancement request, please ensure:

- [ ] I have identified the specific rule and file
- [ ] I have provided clear examples of current issues
- [ ] I have proposed specific improvements
- [ ] I have included test cases
- [ ] I have assessed the impact and complexity
- [ ] I have considered backward compatibility
- [ ] I have outlined testing requirements

## 🏷️ Labels

<!-- The following labels will be automatically applied based on the issue content -->
<!-- You can also suggest additional labels -->

- **Type**: Rule Enhancement
- **Priority**: [Critical/High/Medium/Low]
- **Component**: Rules
- **WordPress**: [Core/Plugin/Theme/Multisite]
- **Complexity**: [Simple/Moderate/Complex]
