# symfony-pha
### Console command for commission fee calculation

### Installation
1. Clone the Repository
2. Install `make` to be able to run commands through a Makefile
3. Run `make install`, this command will run: 
    - up docker container
    - create .env file from env.example
    - composer install (from container)
4. Add to .env `CURRENCY_EXCHANGE_SERVICE_API_KEY=''` for http://api.exchangeratesapi.io/latest API Calls

### Configuration for calculation
1. Calculation configuration stored in:
```
.env
```

### Run the calculation command:
1. `cd` to the project directory.
2. Create in the project root directory input.csv file and fill it, or you can run `make create-input-csv-file`

   NOTE: input.csv file name added .gitignore
3. run the console on of this command:
   ```
   make calculate-via-docker - Command execution through a docker container
   
   make calculate-via-symfony - Command execution through the Symfony console
   
   make calculate-via-php - ## Command execution through locally installed php
   ```
4. If you want set custom input.csv file path you can run one of this command where you can set input file path:
   ```
   symfony console app:calculate-commission ./input.csv
   or
   php ./bin/console app:calculate-commission ./input.csv
   ```

### Testing
1. Unit test command:
```
make phpunit
```
