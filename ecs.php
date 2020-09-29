<?php
declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\MemberVarSpacingSniff;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrailingCommaInMultilineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\MethodSeparationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\Comment\HashToSlashCommentFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\PreIncrementFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocInlineTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAccessFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoAliasTagFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoEmptyReturnFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoPackageFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocNoUselessInheritdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocScalarFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocSingleLineVarSpacingFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocToCommentFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTrimFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocTypesFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocVarWithoutNameFixer;
use PhpCsFixer\Fixer\Semicolon\NoEmptyStatementFixer;
use PhpCsFixer\Fixer\Semicolon\NoSinglelineWhitespaceBeforeSemicolonsFixer;
use PhpCsFixer\Fixer\Semicolon\SemicolonAfterInstructionFixer;
use PhpCsFixer\Fixer\Semicolon\SpaceAfterSemicolonFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraConsecutiveBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;
use SlevomatCodingStandard\Sniffs\Commenting\RequireOneLinePropertyDocCommentSniff;
use SlevomatCodingStandard\Sniffs\Exceptions\ReferenceThrowableOnlySniff;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

require_once __DIR__ . '/vendor/symplify/easy-coding-standard/config/set/psr12.php';
require_once __DIR__ . '/vendor/symplify/easy-coding-standard/config/set/clean-code.php';

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(ConcatSpaceFixer::class)
        ->call('configure', [['spacing' => 'one']]);

    $services->set(NewWithBracesFixer::class);

    $services->set(PhpdocAlignFixer::class)
        ->tag('param');

    $services->set(UnaryOperatorSpacesFixer::class);

    $services->set(CastSpacesFixer::class);

    $services->set(DeclareEqualNormalizeFixer::class);

    $services->set(HashToSlashCommentFixer::class);

    $services->set(IncludeFixer::class);

    $services->set(LowercaseCastFixer::class);

    $services->set(MethodSeparationFixer::class);

    $services->set(NativeFunctionCasingFixer::class);

    $services->set(NoBlankLinesAfterClassOpeningFixer::class);

    $services->set(NoBlankLinesAfterPhpdocFixer::class);

    $services->set(NoEmptyCommentFixer::class);

    $services->set(NoEmptyPhpdocFixer::class);

    $services->set(NoEmptyStatementFixer::class);

    $services->set(NoExtraConsecutiveBlankLinesFixer::class)
        ->call('configure', [['curly_brace_block', 'extra', 'parenthesis_brace_block', 'square_brace_block', 'throw', 'use']]);

    $services->set(NoLeadingNamespaceWhitespaceFixer::class);

    $services->set(NoMultilineWhitespaceAroundDoubleArrowFixer::class);

    $services->set(NoShortBoolCastFixer::class);

    $services->set(NoSinglelineWhitespaceBeforeSemicolonsFixer::class);

    $services->set(NoSpacesAroundOffsetFixer::class);

    $services->set(NoTrailingCommaInListCallFixer::class);

    $services->set(NoTrailingCommaInSinglelineArrayFixer::class);

    $services->set(TrailingCommaInMultilineArrayFixer::class);

    $services->set(NoUnneededControlParenthesesFixer::class);

    $services->set(NoWhitespaceBeforeCommaInArrayFixer::class);

    $services->set(NoWhitespaceInBlankLineFixer::class);

    $services->set(NormalizeIndexBraceFixer::class);

    $services->set(ObjectOperatorWithoutWhitespaceFixer::class);

    $services->set(PhpdocAnnotationWithoutDotFixer::class);

    $services->set(PhpdocIndentFixer::class);

    $services->set(PhpdocInlineTagFixer::class);

    $services->set(PhpdocNoAccessFixer::class);

    $services->set(PhpdocNoEmptyReturnFixer::class);

    $services->set(PhpdocNoPackageFixer::class);

    $services->set(PhpdocNoUselessInheritdocFixer::class);

    $services->set(PhpdocScalarFixer::class);

    $services->set(PhpdocSingleLineVarSpacingFixer::class);

    $services->set(PhpdocToCommentFixer::class);

    $services->set(PhpdocTrimFixer::class);

    $services->set(PhpdocTypesFixer::class);

    $services->set(PhpdocVarWithoutNameFixer::class);

    $services->set(PreIncrementFixer::class);

    $services->set(ReturnTypeDeclarationFixer::class)
        ->call('configure', [['space_before' => 'none']]);

    $services->set(SelfAccessorFixer::class);

    $services->set(ShortScalarCastFixer::class);

    $services->set(SingleQuoteFixer::class);

    $services->set(SpaceAfterSemicolonFixer::class);

    $services->set(StandardizeNotEqualsFixer::class);

    $services->set(TernaryOperatorSpacesFixer::class);

    $services->set(TrimArraySpacesFixer::class);

    $services->set(WhitespaceAfterCommaInArrayFixer::class);

    $services->set(ClassDefinitionFixer::class)
        ->call('configure', [['single_line' => true]]);

    $services->set(MagicConstantCasingFixer::class);

    $services->set(MethodArgumentSpaceFixer::class);

    $services->set(NoMixedEchoPrintFixer::class)
        ->call('configure', [['use' => 'echo']]);

    $services->set(PhpUnitFqcnAnnotationFixer::class);

    $services->set(PhpdocNoAliasTagFixer::class);

    $services->set(ProtectedToPrivateFixer::class);

    $services->set(SingleBlankLineBeforeNamespaceFixer::class);

    $services->set(SingleClassElementPerStatementFixer::class);

    $services->set(NoUnneededCurlyBracesFixer::class);

    $services->set(NoUnneededFinalMethodFixer::class);

    $services->set(SemicolonAfterInstructionFixer::class);

    $services->set(YodaStyleFixer::class);

    $services->set(RandomApiMigrationFixer::class)
        ->call('configure', [['mt_rand' => 'random_int', 'rand' => 'random_int']]);

    $services->set(DeclareStrictTypesFixer::class);

    $services->set(ReferenceThrowableOnlySniff::class);

    $services->set(RequireOneLinePropertyDocCommentSniff::class);

    $services->set(DocCommentSpacingSniff::class)
        ->property('linesCountBeforeFirstContent', 0)
        ->property('linesCountBetweenDifferentAnnotationsTypes', 1);

    $services->set(MemberVarSpacingSniff::class)
        ->property('spacing', 1)
        ->property('spacingBeforeFirst', 0);

    $services->set(HeaderCommentFixer::class)
        ->call('configure', [['header' => 'Licensed under MIT. See file /LICENSE.', 'location' => 'after_declare_strict']]);

    $services->set(NativeFunctionInvocationFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('skip', ['SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff.UselessDocComment' => ['tests/*'], 'PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff.NoLineAfterClose' => ['./*']]);
};
