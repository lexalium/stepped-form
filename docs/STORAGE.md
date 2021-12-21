## Storage

A storage stores the current step and data handled by each step. You can
create your own storage by implementing a 
`Lexal\SteppedForm\Data\Storage\StorageInterface` interface. And 
then inject it into the 
[Form Data Storage](FORM_STATE.md#form-data-storage) and 
[Step Control](FORM_STATE.md#step-control).

There is only one simple storage implemented in the package: 
`Lexal\SteppedForm\Data\Storage\ArrayStorage`.
