# Changelog

All notable changes to `kolaybi/numerator` will be documented in this file.

---

### v1.3.0 (2025-07-08)
- **Added Group Support for NumeratorType**
  - Added `group` column to `numerator_types` table for better categorization
  - Enhanced organization and filtering capabilities for numerator types

- **Introduced `reuse_if_deleted` Functionality** 
  - Added `reuse_if_deleted` column to `numerator_profiles` table with configuration support
  - Implemented soft-deleted sequence reuse logic in NumeratorProfileService
  - Enhanced sequence creation and validation to respect reuse policies
  - Fixed critical bug in `getNextAvailableNumber()` method to properly handle deleted sequences

- **Database Schema Improvements**
  - Removed unique constraint from `numerator_sequences` table for more flexible combinations
  - Allows multiple sequences with same formatted number across different contexts

- **Service Layer Enhancements**
  - Refactored NumeratorProfileService to handle active and deleted sequences separately  
  - Added `hasActiveSequence()` and `hasDeletedSequence()` helper methods
  - Improved sequence checking logic with proper `reuse_if_deleted` compliance
  - Optimized `getNextAvailableNumber()` performance with O(1) lookup using `array_flip()`

- **Enhanced Test Coverage**
  - Added comprehensive test coverage for `reuse_if_deleted` functionality
  - Implemented test scenarios for active and deleted sequence handling
  - Added factory helper methods for reuse configuration (`withReuse()`)
  - Achieved 100% test coverage for NumeratorProfileService and NumeratorProfile
  - Created regression tests to prevent future bugs in sequence reuse logic

- **Code Quality Improvements**
  - Eliminated code duplication in sequence validation logic
  - Enhanced code comments and documentation for better maintainability
  - Applied consistent coding standards and optimizations

### v1.2.0 (2025-06-18)
- **Added `is_active` support for NumeratorProfile**
  - Added `is_active` column to `numerator_profiles` table (defaults to false)
  - Added boolean cast for `is_active` field in NumeratorProfile model
  - Implemented `onlyActive` parameter in profile retrieval methods
  - Added `skipActiveCheck` parameter in sequence creation
  - Enhanced NumeratorProfileService with active status filtering
  - Added comprehensive test coverage for active/inactive scenarios

- **Improved Configuration Management**
  - Made configuration values fully environment-driven
  - Removed hardcoded defaults in Config::get calls across migrations, models, and factories
  - Updated config and language publish paths to include kolaybi namespace

- **Enhanced Factory Support**
  - Added `withActive()` and `withInactive()` helper methods to NumeratorProfileFactory
  - Implemented configurable default for `is_active` in factory

- **Service Layer Improvements**
  - Added `onlyActive` filtering to `getNumeratorProfiles()`, `findNumeratorProfile()`, `findNumeratorProfileByType()`, and `getCounter()` methods
  - Enhanced `createNumeratorSequence()` with active status validation
  - Expanded Illuminate package version support compatibility

- **Test Coverage Enhancements**
  - Added comprehensive test cases for active/inactive profile scenarios
  - Enhanced factory testing with new helper methods
  - Achieved 100% test coverage for all service classes

- **Development Tools Updates**
  - Updated Laravel Pint from v1.13 to v1.22.1
  - Enhanced pint.json configuration with new code style rules
  - Added modern PHP coding standards and best practices
  - Improved code formatting rules for better consistency

### v1.1.0 (2024-02-13)
- Added suffix and padding for `NumeratorProfile::formattedNumber`
- Added default prefix, suffix, padding and format for `NumeratorType`
- Added format checks 

### v1.0.0 (2024-02-02)
- Initial release

---
