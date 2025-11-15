# Contributing to CJ Affiliate SDK for PHP

First off, thank you for considering contributing to CJ Affiliate SDK! It's people like you that make this SDK better for everyone.

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When you create a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples**
- **Describe the behavior you observed and what you expected**
- **Include PHP version and environment details**

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a detailed description of the suggested enhancement**
- **Explain why this enhancement would be useful**
- **List any examples of how it would be used**

### Pull Requests

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Commit your changes (`git commit -m 'Add some amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

## Development Guidelines

### Code Style

- Follow PSR-4 autoloading standards
- Use meaningful variable and function names
- Add comments for complex logic
- Keep functions small and focused
- Use type hints where possible

### Example Code Style

```php
<?php

namespace CJAffiliate\API;

/**
 * Class description
 * 
 * @package CJAffiliate\API
 */
class ExampleAPI
{
    /**
     * Method description
     * 
     * @param array $params Parameters
     * @return array
     * @throws CJException
     */
    public function exampleMethod(array $params): array
    {
        // Implementation
    }
}
```

### Commit Messages

- Use the present tense ("Add feature" not "Added feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit the first line to 72 characters or less
- Reference issues and pull requests liberally after the first line

### Documentation

- Update README.md if you change functionality
- Add examples for new features
- Update CHANGELOG.md
- Document all public methods

### Testing

Before submitting a pull request:

1. Test your changes thoroughly
2. Ensure backward compatibility
3. Test with different PHP versions (7.4+)
4. Verify error handling works correctly

## Project Structure

```
cj-sdk-php/
├── src/
│   ├── API/              # API implementations
│   ├── Exceptions/       # Custom exceptions
│   ├── Utils/            # Utility classes
│   └── CJClient.php      # Main client
├── examples/             # Usage examples
├── tests/                # Future tests directory
└── docs/                 # Additional documentation
```

## Adding New API Endpoints

To add a new API endpoint:

1. Create a new class in `src/API/`
2. Extend the base functionality
3. Add method to `CJClient.php`
4. Create example in `examples/`
5. Update README.md
6. Update CHANGELOG.md

### Example:

```php
// src/API/NewAPI.php
namespace CJAffiliate\API;

use CJAffiliate\CJClient;
use CJAffiliate\Utils\HttpClient;
use CJAffiliate\Exceptions\CJException;

class NewAPI
{
    private $client;
    private $http;
    
    public function __construct(CJClient $client)
    {
        $this->client = $client;
        $this->http = new HttpClient(
            $client->getAccessToken(),
            $client->getConfig('timeout', 30)
        );
    }
    
    public function someMethod(array $params): array
    {
        // Implementation
    }
}
```

## Questions?

Feel free to open an issue with the tag "question" if you have any questions about contributing.

## Recognition

Contributors will be recognized in the README.md file.

Thank you for your contributions! 🚀
