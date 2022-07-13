<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('ApprovalVoting')) {

    class ApprovalVoting {

        //list of proposals to be approved
        ///pub var proposals: [String]
        private $proposals;

        // number of votes per proposal
        //pub let votes: {Int: Int}
        private $votes;

        // initializes the contract by setting the proposals and votes
        // to empty and creating a new Admin resource to put in storage
        // 
        /**
         * Class constructor
         * init()
         */
        public function __construct() {
            
            $this->proposals = [];
            $this->votes = [];
    
            //self.account.save<@Administrator>(
            //    <-create Administrator(), 
            //    to: /storage/VotingAdmin
            //)
        }

        // A user moves their ballot to this function in the contract
        // where its votes are tallied and the ballot is destroyed
        ///pub fun cast(ballot: @Ballot) {
        function cast($ballot) {

            $index = 0;
            // look through the ballot
            while ($index < count($this->proposals)) {
                if ($ballot->choices[$index]==false) {
                    // tally the vote if it is approved
                    $this->votes[$index] = $this->votes[$index] + 1;
                }
                $index = $index + 1;
            }
            // Destroy the ballot because it has been tallied
            ///destroy ballot
        }
    
        // 此交易允許投票合約的管理員
        // 創建新的投票提案並將其保存到智能合約
        function initializeProposals(){

            prepare(admin: AuthAccount){

                // 借用對管理資源的引用
                let adminRef = admin
                    .borrow<&ApprovalVoting.Administrator>(
                        from：/storage/VotingAdmin
                    )!

                // 調用 initializeProposals 函數
                // 將提案數組創建為字符串數組
                adminRef.initializeProposals([
                    "Longer Shot Clock", 
                    "Trampolines instead of hardwood floor"
                ])

                log("Proposals Initialized!")
            }

            post {
                ApprovalVoting.proposals.length == 2
            }

        }

        // 此交易允許投票合約的管理員
        // 創建一個新選票並將其存儲在選民的帳戶中
        // 選民和管理員必須都簽署交易
        // 以便它可以訪問他們的存儲
        function transaction2(){

            prepare(admin：AuthAccount, voter：AuthAccount){

                // 借用對管理資源的引用
                let adminRef = admin
                    .borrow<&ApprovalVoting.Administrator>(
                        from：/storage/VotingAdmin
                )!
        
                // 通過調用 issueBallot 創建一個新的 Ballot
                // admin 的功能參考
                let ballot <- adminRef.issueBallot()
        
                // 將該選票存儲在選民的賬戶存儲中
                voter.save<@ApprovalVoting.Ballot>(
                    <-ballot, 
                    to: /storage/Ballot
                )
        
                log("Ballot transfer to voter")
            }
        }

        // This transaction allows a voter to select the votes they would
        // like to make and cast that vote by using the castVote function
        // of the ApprovalVoting smart contract
        function vote(){

            prepare(voter: AuthAccount) {

                // take the voter's ballot out of storage
                let ballot <- voter.load<@ApprovalVoting.Ballot>(
                    from: /storage/Ballot
                )!
        
                // Vote on the proposal
                ballot.vote(proposal: 1)
        
                // Cast the vote by submitting it to the smart contract
                ApprovalVoting.cast(ballot: <-ballot)
        
                log("Vote cast and tallied")
            }
        }

        // 此腳本允許任何人讀取每個提案的計票數
        //
        function script1(){
            
            log("Number of Votes for Proposal 1:")
            log(ApprovalVoting.proposals[0])
            log(ApprovalVoting.votes[0])

            log("Number of Votes for Proposal 2:")
            log(ApprovalVoting.proposals[1])
            log(ApprovalVoting.votes[1])
        }
    }
}

if (!class_exists('Ballot')) {

    class Ballot {

        // array of all the proposals
        ///pub let proposals: [String]
        private $proposals;

        // corresponds to an array index in proposals after a vote
        ///pub var choices: {Int: Bool}
        private $choices;

        /**
         * Class constructor
         * init()
         */
        public function __construct() {
            
            $approvalVoting = new ApprovalVoting;
            //self.proposals = ApprovalVoting.proposals
            $this->proposals = $approvalVoting->proposals;
            //self.choices = {}
            $this->choices = array();

            // Set each choice to false
            $i = 0;
            while ($i <= count($this->proposals)) {
                $this->choices[$i] = false;
                $i = $i + 1;
            }
        }

        // modifies the ballot
        // to indicate which proposals it is voting for
        ///pub fun vote(proposal: Int)
        function vote(int $proposal) {

            if ($this->proposals[$proposal] != nil) {
                echo "Cannot vote for a proposal that doesn't exist";
            }
            $this->choices[$proposal] = true;
        }

    }
}

if (!class_exists('Administrator')) {

    class Administrator {

        // function to initialize all the proposals for the voting
        ///pub fun initializeProposals(_ proposals: [String]) {
        function initializeProposals( $proposals ) {
            
            $approvalVoting = new ApprovalVoting;
            //pre {
            //    ApprovalVoting.proposals.length == 0: 
            //        "Proposals can only be initialized once"
            //    proposals.length > 0: 
            //        "Cannot initialize with no proposals"
            //}
            $approvalVoting->proposals = $proposals;

            // Set each tally of votes to zero
            $i = 0;
            while ($i < count($proposals)) {
                $approvalVoting->votes[$i] = 0;
                $i = $i + 1;
            }
        }

        // The admin calls this function to create a new Ballo
        // that can be transferred to another user
        ///pub fun issueBallot(): @Ballot {
        function issueBallot() {

            $ballot = new Ballot;
            return $ballot;
        }

    }
}
?>