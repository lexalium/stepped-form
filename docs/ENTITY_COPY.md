# Entity Copy

The Stepped Form uses an Entity Copy for copying an entity passing its new
copy to the step `handle` method and saving one to the storage. Steps will
work with its own entity copy.

The package already has `Lexal\SteppedForm\EntityCopy\SimpleEntityCopy`
class that implements `Lexal\SteppedForm\EntityCopy\EntityCopyInterface`. But
you have to implement `__clone` method to clone internal objects if you use
objects as form entity (this is due to how PHP stores objects in memory).

See [interface definition](../src/EntityCopy/EntityCopyInterface.php)
for more details.
