<?php
declare(strict_types=1);

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Functions\MultiLineFunctionDeclarationSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\MemberVarSpacingSniff;
use PhpCsFixer\Fixer\Alias\NoMixedEchoPrintFixer;
use PhpCsFixer\Fixer\Alias\RandomApiMigrationFixer;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoMultilineWhitespaceAroundDoubleArrowFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoTrailingCommaInSinglelineArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NoWhitespaceBeforeCommaInArrayFixer;
use PhpCsFixer\Fixer\ArrayNotation\NormalizeIndexBraceFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\ArrayNotation\WhitespaceAfterCommaInArrayFixer;
use PhpCsFixer\Fixer\Casing\MagicConstantCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\CastNotation\LowercaseCastFixer;
use PhpCsFixer\Fixer\CastNotation\NoShortBoolCastFixer;
use PhpCsFixer\Fixer\CastNotation\ShortScalarCastFixer;
use PhpCsFixer\Fixer\ClassNotation\ClassDefinitionFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SingleClassElementPerStatementFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\Comment\NoEmptyCommentFixer;
use PhpCsFixer\Fixer\Comment\SingleLineCommentStyleFixer;
use PhpCsFixer\Fixer\ControlStructure\IncludeFixer;
use PhpCsFixer\Fixer\ControlStructure\NoTrailingCommaInListCallFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\FunctionNotation\ReturnTypeDeclarationFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\NamespaceNotation\NoLeadingNamespaceWhitespaceFixer;
use PhpCsFixer\Fixer\NamespaceNotation\SingleBlankLineBeforeNamespaceFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\ConcatSpaceFixer;
use PhpCsFixer\Fixer\Operator\IncrementStyleFixer;
use PhpCsFixer\Fixer\Operator\NewWithBracesFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\StandardizeNotEqualsFixer;
use PhpCsFixer\Fixer\Operator\TernaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Phpdoc\NoSuperfluousPhpdocTagsFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitFqcnAnnotationFixer;
use PhpCsFixer\Fixer\Phpdoc\NoBlankLinesAfterPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\NoEmptyPhpdocFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAnnotationWithoutDotFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocIndentFixer;
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
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\NoExtraBlankLinesFixer;
use PhpCsFixer\Fixer\Whitespace\NoSpacesAroundOffsetFixer;
use PhpCsFixer\Fixer\Whitespace\NoTrailingWhitespaceFixer;
use PhpCsFixer\Fixer\Whitespace\NoWhitespaceInBlankLineFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([SetList::CLEAN_CODE]);
    $ecsConfig->sets([SetList::PSR_12]);

    $ecsConfig->ruleWithConfiguration(ConcatSpaceFixer::class, ['spacing' => 'one']);

    $ecsConfig->rule(NewWithBracesFixer::class);

    $ecsConfig->ruleWithConfiguration(PhpdocAlignFixer::class, ['tags' => ['param']]);

    $ecsConfig->rule(UnaryOperatorSpacesFixer::class);

    $ecsConfig->rule(CastSpacesFixer::class);

    $ecsConfig->rule(DeclareEqualNormalizeFixer::class);

    $ecsConfig->ruleWithConfiguration(SingleLineCommentStyleFixer::class, ['comment_types' => ['hash']]);

    $ecsConfig->rule(IncludeFixer::class);

    $ecsConfig->rule(LowercaseCastFixer::class);

    $ecsConfig->rule(NativeFunctionCasingFixer::class);

    $ecsConfig->rule(NoBlankLinesAfterClassOpeningFixer::class);

    $ecsConfig->rule(NoBlankLinesAfterPhpdocFixer::class);

    $ecsConfig->rule(NoEmptyCommentFixer::class);

    $ecsConfig->rule(NoEmptyPhpdocFixer::class);

    $ecsConfig->rule(NoEmptyStatementFixer::class);

    $ecsConfig->rule(NoLeadingNamespaceWhitespaceFixer::class);

    $ecsConfig->rule(NoMultilineWhitespaceAroundDoubleArrowFixer::class);

    $ecsConfig->rule(NoShortBoolCastFixer::class);

    $ecsConfig->rule(NoSinglelineWhitespaceBeforeSemicolonsFixer::class);

    $ecsConfig->rule(NoSpacesAroundOffsetFixer::class);

    $ecsConfig->rule(NoTrailingCommaInListCallFixer::class);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, ['syntax' => 'short']);

    $ecsConfig->rule(NoTrailingCommaInSinglelineArrayFixer::class);

    $ecsConfig->rule(TrailingCommaInMultilineFixer::class);

    $ecsConfig->rule(MultiLineFunctionDeclarationSniff::class);

    $ecsConfig->rule(NoUnneededControlParenthesesFixer::class);

    $ecsConfig->rule(NoWhitespaceBeforeCommaInArrayFixer::class);

    $ecsConfig->rule(NoWhitespaceInBlankLineFixer::class);

    $ecsConfig->rule(NormalizeIndexBraceFixer::class);

    $ecsConfig->rule(ObjectOperatorWithoutWhitespaceFixer::class);

    $ecsConfig->rule(PhpdocAnnotationWithoutDotFixer::class);

    $ecsConfig->rule(PhpdocIndentFixer::class);

    $ecsConfig->rule(PhpdocNoAccessFixer::class);

    $ecsConfig->rule(PhpdocNoEmptyReturnFixer::class);

    $ecsConfig->rule(PhpdocNoPackageFixer::class);

    $ecsConfig->rule(PhpdocNoUselessInheritdocFixer::class);

    $ecsConfig->rule(PhpdocScalarFixer::class);

    $ecsConfig->rule(PhpdocSingleLineVarSpacingFixer::class);

    $ecsConfig->rule(PhpdocToCommentFixer::class);

    $ecsConfig->rule(PhpdocTrimFixer::class);

    $ecsConfig->rule(PhpdocTypesFixer::class);

    $ecsConfig->rule(PhpdocVarWithoutNameFixer::class);

    $ecsConfig->ruleWithConfiguration(IncrementStyleFixer::class, ['style' => 'pre']);

    $ecsConfig->ruleWithConfiguration(ReturnTypeDeclarationFixer::class, ['space_before' => 'none']);

    $ecsConfig->rule(SelfAccessorFixer::class);

    $ecsConfig->rule(ShortScalarCastFixer::class);

    $ecsConfig->rule(SingleQuoteFixer::class);

    $ecsConfig->rule(SpaceAfterSemicolonFixer::class);

    $ecsConfig->rule(StandardizeNotEqualsFixer::class);

    $ecsConfig->rule(TernaryOperatorSpacesFixer::class);

    $ecsConfig->rule(TrimArraySpacesFixer::class);

    $ecsConfig->rule(WhitespaceAfterCommaInArrayFixer::class);

    $ecsConfig->ruleWithConfiguration(ClassDefinitionFixer::class, ['single_line' => true]);

    $ecsConfig->rule(MagicConstantCasingFixer::class);

    $ecsConfig->rule(MethodArgumentSpaceFixer::class);

    $ecsConfig->ruleWithConfiguration(NoMixedEchoPrintFixer::class, ['use' => 'echo']);

    $ecsConfig->rule(PhpUnitFqcnAnnotationFixer::class);

    $ecsConfig->rule(PhpdocNoAliasTagFixer::class);

    $ecsConfig->rule(ProtectedToPrivateFixer::class);

    $ecsConfig->rule(SingleBlankLineBeforeNamespaceFixer::class);

    $ecsConfig->rule(SingleClassElementPerStatementFixer::class);

    $ecsConfig->rule(NoUnneededCurlyBracesFixer::class);

    $ecsConfig->rule(NoUnneededFinalMethodFixer::class);

    $ecsConfig->rule(SemicolonAfterInstructionFixer::class);

    $ecsConfig->rule(YodaStyleFixer::class);

    $ecsConfig->rule(NoUnusedImportsFixer::class);

    $ecsConfig->rule(OrderedImportsFixer::class);

    $ecsConfig->rule(ArrayIndentationFixer::class);

    $ecsConfig->ruleWithConfiguration(RandomApiMigrationFixer::class, ['replacements' => ['mt_rand' => 'random_int', 'rand' => 'random_int']]);

    $ecsConfig->rule(DeclareStrictTypesFixer::class);

    $ecsConfig->rule(NoTrailingWhitespaceFixer::class);

    $ecsConfig->ruleWithConfiguration(NoExtraBlankLinesFixer::class, ['tokens' => ['curly_brace_block', 'case', 'return', 'use', 'throw', 'switch', 'parenthesis_brace_block', 'extra', 'default']]);

    $ecsConfig->rule(NoSuperfluousPhpdocTagsFixer::class);

    $ecsConfig->ruleWithConfiguration(MemberVarSpacingSniff::class, ['spacing' => 1, 'spacingBeforeFirst' => 0]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, ['header' => 'Licensed under MIT. See file /LICENSE.', 'location' => 'after_declare_strict']);

    $ecsConfig->ruleWithConfiguration(NativeFunctionInvocationFixer::class, ['include' => ['@all'], 'scope' => 'all']);

    $ecsConfig->skip([
        'SlevomatCodingStandard\Sniffs\TypeHints\TypeHintDeclarationSniff.UselessDocComment' => [
            'tests/*',
        ],
        'PHP_CodeSniffer\Standards\Squiz\Sniffs\WhiteSpace\ControlStructureSpacingSniff.NoLineAfterClose' => [
            './*',
        ],
        BinaryOperatorSpacesFixer::class => [
            './*',
        ]
    ]);
};
