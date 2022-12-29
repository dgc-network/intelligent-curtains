<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}
if (!class_exists('NonFungibleToken')) {

    class NonFungibleToken {

        // The total number of tokens of this type in existence
        private $totalSupply;
        private $INFT;
        private $Provider;
        private $Receiver;
        private $CollectionPublic;

        /**
         * Class constructor
         * init()
         */
        public function __construct() {
            
            // Initialize the total supply
            $this->totalSupply = 0;

            //$INFT = new NFT;
            $Provider = new NFT_Collection;
            $Receiver = new NFT_Collection;
            $CollectionPublic = new NFT_Collection;

            // Create a Collection resource and save it to storage
            $collection = create_Collection();
            //self.account.save(<-collection, to: /storage/NFTCollection)

            // create a public capability for the collection
            //self.account.link<&{NonFungibleToken.CollectionPublic}>(
            //    /public/NFTCollection,
            //    target: /storage/NFTCollection
            //)

            // Create a Minter resource and save it to storage
            $minter = create_NFTMinter();
            //self.account.save(<-minter, to: /storage/NFTMinter)

            //emit ContractInitialized()
            event_ContractInitialized();
        }

        // Event that emitted when the NFT contract is initialized
        function event_ContractInitialized() {}

        // Event that is emitted when a token is withdrawn, indicating the owner
        // of the collection that it was withdrawn from.
        //
        // If the collection is not in an account's storage, `from` will be `nil`.
        function event_Withdraw(int $id, int $from){}

        // Event that emitted when a token is deposited to a collection.
        //
        // It indicates the owner of the collection that it was deposited to.
        function event_Deposit(int $id, int $to){}

        /**
         * Transactions
         */
        // This transaction is what an account would run to set itself up to receive NFTs
        function setup_account(){}

        // This transaction is for transferring and NFT from one account to another
        ///transaction(recipient: Address, withdrawID: UInt64)
        function transfer_nft( $recipient, int $withdrawID ){
/*
            // get the recipients public account object
            let recipient = getAccount(recipient)

            // borrow a reference to the signer's NFT collection
            let collectionRef = acct.borrow<&ExampleNFT.Collection>(
                from: /storage/NFTCollection
            )?? panic("Could not borrow a reference to the owner's collection")

            // borrow a public reference to the receivers collection
            let depositRef = recipient.getCapability(/public/NFTCollection)
                .borrow<&{NonFungibleToken.CollectionPublic}>()
                ?? panic("Could not borrow a reference to the receiver's collection")

            // withdraw the NFT from the owner's collection
            let nft <- collectionRef.withdraw(withdrawID: withdrawID)

            // Deposit the NFT in the recipient's collection
            depositRef.deposit(token: <-nft)
*/
        }

        // This script uses the NFTMinter resource to mint a new NFT
        ///transaction(recipient: Address)
        function mint_nft( $recipient ){

           // create a new NFT
           $newNFT = new NFT($this->totalSupply);

           // deposit it in the recipient's account using their reference
           $recipient->deposit( $newNFT );

           $this->totalSupply = $this->totalSupply + 1;
        }

        /**
         * Scripts
         */
        // This transaction returns an array of all the nft ids in the collection
        ///pub fun main(account: Address): [UInt64]
        function read_collection_ids( $account ){
/*
            let collectionRef = getAccount(account)
                .getCapability(/public/%s)
                .borrow<&{NonFungibleToken.CollectionPublic}>()
                ?? panic("Could not borrow capability from public collection")
     
            return collectionRef.getIDs()
*/
            return $this->CollectionPublic.getIDs();
        }


        // This transaction gets the length of an account's nft collection
        ///pub fun main(account: Address): Int
        function read_collection_length( $account ){

        }

        // This script reads metadata about an NFT in a user's collection
        ///pub fun main(account: Address): UInt64 
        function read_nft_id( $account ){
/*            
            // Get the public collection of the owner of the token
            let collectionRef = getAccount(account)
                .getCapability(/public/NFTCollection)
                .borrow<&{NonFungibleToken.CollectionPublic}>()
                ?? panic("Could not borrow capability from public collection")

            // Borrow a reference to a specific NFT in the collection
            let nft = collectionRef.borrowNFT(id: 1)

            return nft.id
*/
        }

        // public function that anyone can call to create a new empty collection
        ///pub fun createEmptyCollection(): @NonFungibleToken.Collection 
        function createEmptyCollection(){
            $collection = new NFT_Collection;
            return $collection;
        }
    }
}

if (!class_exists('NFT')) {

    class NFT {

        private $id;
        private $metadata;
 
        /**
         * Class constructor
         * init()
         */
        public function __construct( $initID ) {
            $this->id = $initID;
            $this->metadata = array();
        }
     }
}

if (!class_exists('NFT_Collection')) {

    class NFT_Collection {

        // NFT is a resource type with an `UInt64` ID field
        ///pub var ownedNFTs: @{UInt64: NonFungibleToken.NFT}
        private $ownedNFTs;

        /**
         * Class constructor
         * init()
         */
        public function __construct() {
            $this->ownedNFTs = array();
        }

        // withdraw removes an NFT from the collection and moves it to the 
        // caller
        ///pub fun withdraw(withdrawID: UInt64): @NonFungibleToken.NFT 
        function withdraw( $withdrawID ){

            $token = self.ownedNFTs.remove($withdrawID);

            event_Withdraw($token.id, self.owner.address);

            return $token;
        }
        
        // deposit takes a NFT and adds it to the collections dictionary
        // and adds the ID to the id array
        ///pub fun deposit(token: @NonFungibleToken.NFT)
        function deposit( $token ){

            //let token <- token as! @ExampleNFT.NFT

            $id = $token.id;
 
            // add the new token to the dictionary which removes the old one
            //let oldToken <- self.ownedNFTs[id] <- token
 
            //to: self.owner?.address
            event_Deposit($token.id, self.owner.address);
 
            //destroy oldToken
        }

        // getIDs returns an array of the IDs that are in the collection
        ///pub fun getIDs(): [UInt64] 
        function getIDs(){
            //return self.ownedNFTs.keys;
            return $this->ownedNFTs;
        }

        // borrowNFT gets a reference to an NFT in the collection
        // so that the caller can read its metadata and call its methods
        //pub fun borrowNFT(id: UInt64): &NonFungibleToken.NFT 
        function borrowNFT(int $id){
            //return &self.ownedNFTs[id] as &NonFungibleToken.NFT
            return $this->ownedNFTs[$id];
        }

        //destroy() {
        //    destroy self.ownedNFTs
        //}
        //destroy($this->ownedNFTs);
    }
}

if (!class_exists('NFT_Minter')) {

    class NFT_Minter {

        // mintNFT mints a new NFT with a new ID and deposit it in the
        // recipients collection using their collection reference
        //
        ///pub fun mintNFT(recipient: &{NonFungibleToken.CollectionPublic}) 
        function mintNFT($recipient){
            
        }

    }
}
?>