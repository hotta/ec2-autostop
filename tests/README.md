# Class inheritance diagram

```
PHPUnit\Framework\Assert
└ PHPUnit\Framework\TestCase
   └ Illuminate\Foundation\Testing\TestCase
      └ tests/TestCase
         ├ tests/Feature/Ec2TestCase   - use DatabaseTransactions
         │ ├ tests/Feature/Ec2CommandTestCase  - execute()
         │ │ └ tests/Feature/Ec2StartCommandTest
         │ └ tests/Feature/Ec2ManualsControllerTest 
```
