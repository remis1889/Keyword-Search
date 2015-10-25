# Keyword-Search-Engine

Keyword search has become a ubiquitous method for users to access text data in the face of information explosion. Type-ahead search predicts a word or phrase that the user may type in based on the partial string the user has typed. Top-k queries are answered by accessing inverted lists on trie leaf nodes. Since inverted lists are usually large, many compression techniques have been proposed to reduce the storage space and disk I/O time. However, these techniques usually perform decompression operations on the fly, which increases the CPU time.

The Generalized INverted IndeX (GINIX) is a more efficient index structure, which merges consecutive IDs in inverted lists into intervals to save storage space. With this index structure, more efficient algorithms can be devised to perform type-ahead keyword search, by taking the advantage of intervals. Specifically, these algorithms do not require conversions from interval lists back to ID lists. As a result, keyword search using GINIX can be more efficient than those using traditional inverted indices. In this project, top-k queries are answered by building a trie on top of GINIX. Trie data structure ensures faster query processing time and GINIX helps in reducing the storage space required for indexing.

Key features : 

1. Exact Search
2. Prefix & Token Matching
3. Ranking
4. Top-k Answers
5. Automatic Query Completion


Index Structures : 

1. Inverted Index
2. Trie
3. Generalized INverted IndeX (GINIX)
4. GINIX + Trie

Ranking Scheme : TF*IDF

The steps carried out for information retrieval can be summarized as follows:

1. Document collection
2. Preprocessing of documents
3. Index construction
4. Process input keywords from user
5. Convert user query to system specific query
6. Retrieve results speci=fic to the query using the index
7. Rank the returned results
