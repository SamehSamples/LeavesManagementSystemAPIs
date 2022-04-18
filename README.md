# Sample 1: Employee Leaves Management System APIs
This is a sample project that focuses on building a backend APIs application using Laravel framework.

The application is an implementation of Kuwaiti Labor law in the part related to employee leaves, explained below (the application business logic).

The application is developed using Repository Pattern to separate the business logic and data access logic and locate them into repositories and to keep code as dry as possible.

The application mainly 	allows employees to check their different leave types balances, apply for different leave types, and allow their direct managers to accept or reject these leaves.

The application also has some basic functionality like: user registration, login, reset password, etc.

It is hosted in AWS EC2 instance, connected to a AWS RDS MySQL database, and utilizing some other AWS services like S3 and SQS.

Please feel free to contact me (sameh74@gmail.com) to share with you the related postman collection if needed.

## What are the frameworks, tools, technologies, approaches, and infrastructure demonstrated in this project?
* Laravel 9
* OOP
* Sanctum
* Separation of concerns and Repository Pattern
* AWS EC2
* AWS IAM
* AWS RDS (MySQL)
* AWS SQS
* AWS S3
* Cron Jobs
* Supervisor (configured and running on AWS EC2 to maintain queued jobs).
## What is Leave Management System?
It is an application that allows organization employees to check their leave balances and apply for leaves in self-service manner. It also allows direct managers to approve/reject their direct subordinates leave applications.  
It is built around the employee leaves as per the Kuwaiti Labor Law, featuring all its leave types.  
The system has the following types of users:
* Individual admins (no employee associated).
* Employees with self-service enabled with admin status.
* Employees with self-service enabled with no admin status.
* Employees with self-service disabled.

Some employees might have records but no self-services accounts (employees who are ICT illiterate), system admins can apply for leaves on behalf of such employees and then the processes will continue normally from there.  
Only Employee’s direct manager can action (approve/reject) their direct subordinates leave applications.  
All Leave related transactions are logged and all Employee related transactions are logged.
## What are the Leave types?
### Annual Leave
* (30 days) per full year of service
* Proportionate parts of the 30 days will be granted for parts of a served year.
* It is only allowed after completing 6 months of service.
* It is fully paid
* Its leave balance is accumulative and can move from one year to another.
* It could be consumed in parts or in full after agreement with direct manager.
### Sick Leaves
* It is granted after providing a genuine medical certificate and as per the number of days explicitly stated in that certificate.
* It is payable as follows per each year of service:
  - 100% for the first 15 days or parts of it (Sick Leave - Tier 1)
  - 75% for the second 15 days or parts of it (Sick Leave - Tier 2)
  - 50% for the third 15 days or parts of it (Sick Leave - Tier 3)
  - 25% for the fourth 15 days or parts of it (Sick Leave - Tier 4)
  - 0% for the days after the first 60 days in the same service year (Sick Leave - Tier 5)
* As per the number of days stated in the medical certificate and its accumulation the employee will get paid for each of the leave days as per the tire that day fall in.
* Each year of service is a new year, in which the counting process starts again from scratch.
### Maternity Leave
* Is allowed only for female employees.
* (90 days) per delivery for 3 times during service period.
* It is fully paid
* Initiated by the child certificate of birth or a genuine medical certificate.
* Given in blocks of 90 days only (not dividable).
### Condolence Leave
* (3 days)
* It is fully paid
* Allowed in the case of the death of relatives from the first-degree only.
* Requires a genuine death certificate.
### Haj Leave
* (5 Days)
* It is fully paid
* Allowed for 1 time only during service.
* Requires confirmed Haj Travel Tickets (and visa if applicable).
## Database
### Database Entities
* User
* Employee
* Leave
* EmployeeLeave (Actual Employee Leave's Transactions)
* Department
* Employee Transaction Log

### Leaves Entity
To be able to properly describe all type of leaves as per the Kuwaiti Labor Law, the *leaves* table was designed as follows:
|Field|Description|
|--|--|
| id |leave id in system|
|name|leave name|
|pay_percentage| the percentage of leave paid salary as per its type|
|default_block_duration_in_days|the leave length in days per the leave's calculation period|
|calculation_period|the period (number of days) in which the leave block is granted. for example: 365 (annually) or null (per service period)|
|allowed_blocks_per_period|number of allowed leave block per period. for example: 3 blocks of Maternity Leaves are allowed during service period|
|days_allowed_after|the number of days of employee service after which the leave is allowed for the employee|
|leave_allowed_after|the id of another leave that must be consumed before the leave is allowed. For example: consuming *Sick Leave - Tier 1* is required before being able to consume *Sick Leave - Tier 2*|
|dividable| should the leave block duration consumed all at once or it could be consumed in parts|
|balance_is_accumulated|could the leave balance be accumulated from one *calculation_period* to another|
|gender_strict|Is the leave gender sensitive, if yes, to which gender. For example: Maternity Leave is allowed only for females|
|fallback_leave|Is this leave the fallback leave. For example: unpaid leave. Only one leave if the leaves table could be set as a fallback leave at the time|
|is_active|is this leave active for future application. The previous leave and applications of a deactivated leave will not be affected after the leave is deactivated|

### Database Diagram
![Database Daigram](https://s3.eu-west-1.amazonaws.com/work-sample1-kw-leave-sys.images.bucket/appOperationalContent/1_202204180623.jpeg)
## Next Steps
* Adding None-working days Entity
* Adding Grade Specific Leave Benefits
* Adding Localization for all text fields
## Contact me
Please feel free to contact me for any clarifications or enquiries at:  
Email: sameh74@gmail.com  
Mobile/WhatsApp: (965)99150372
